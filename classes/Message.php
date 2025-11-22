<?php
/**
 * Message Class
 * Handles all messaging operations including conversations and notifications
 */

class Message {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Send message
     */
    public function send($senderId, $receiverId, $subject, $content) {
        try {
            // Validate users exist
            $sender = $this->db->fetchOne("SELECT * FROM users WHERE id = ? AND is_active = 1", [$senderId]);
            $receiver = $this->db->fetchOne("SELECT * FROM users WHERE id = ? AND is_active = 1", [$receiverId]);
            
            if (!$sender || !$receiver) {
                throw new Exception("Invalid sender or receiver");
            }
            
            // Can't send message to self
            if ($senderId == $receiverId) {
                throw new Exception("Cannot send message to yourself");
            }
            
            // Sanitize content
            $subject = Security::sanitizeInput($subject);
            $content = Security::sanitizeInput($content);
            
            if (empty($content)) {
                throw new Exception("Message content cannot be empty");
            }
            
            // Start transaction
            $this->db->getConnection()->beginTransaction();
            
            // Send message
            $messageId = $this->db->insert('messages', [
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'subject' => $subject,
                'content' => $content
            ]);
            
            // Get or create conversation
            $conversation = $this->getConversation($senderId, $receiverId);
            
            if (!$conversation) {
                // Create new conversation
                $conversationId = $this->db->insert('conversations', [
                    'user1_id' => min($senderId, $receiverId),
                    'user2_id' => max($senderId, $receiverId),
                    'last_message_id' => $messageId
                ]);
            } else {
                // Update existing conversation
                $this->db->update('conversations', 
                    ['last_message_id' => $messageId], 
                    'id = ?', 
                    [$conversation['id']]
                );
            }
            
            // Create notification for receiver
            $this->db->insert('notifications', [
                'user_id' => $receiverId,
                'type' => 'message',
                'title' => 'New Message from ' . $sender['full_name'],
                'message' => Utils::truncateText($content, 50)
            ]);
            
            // Send email notification (if receiver has email notifications enabled)
            // This would require checking user preferences in a real implementation
            
            $this->db->getConnection()->commit();
            
            return ['success' => true, 'message_id' => $messageId];
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            error_log("Send message error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get conversation between two users
     */
    private function getConversation($userId1, $userId2) {
        return $this->db->fetchOne(
            "SELECT * FROM conversations 
             WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)",
            [min($userId1, $userId2), max($userId1, $userId2), min($userId1, $userId2), max($userId1, $userId2)]
        );
    }
    
    /**
     * Get conversation messages
     */
    public function getConversationMessages($userId1, $userId2, $page = 1, $perPage = 20) {
        // Validate both users
        $user1 = $this->db->fetchOne("SELECT * FROM users WHERE id = ? AND is_active = 1", [$userId1]);
        $user2 = $this->db->fetchOne("SELECT * FROM users WHERE id = ? AND is_active = 1", [$userId2]);
        
        if (!$user1 || !$user2) {
            return ['messages' => [], 'pagination' => []];
        }
        
        // Get total count
        $total = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM messages 
             WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)",
            [$userId1, $userId2, $userId2, $userId1]
        )['count'];
        
        // Get pagination info
        $pagination = Utils::paginate($page, $perPage, $total);
        
        // Get messages
        $sql = "SELECT m.*, 
                       s.full_name as sender_name, s.profile_image as sender_image,
                       r.full_name as receiver_name
                FROM messages m 
                JOIN users s ON m.sender_id = s.id 
                JOIN users r ON m.receiver_id = r.id 
                WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
                ORDER BY m.sent_at DESC
                LIMIT {$pagination['offset']}, {$pagination['perPage']}";
        
        $messages = $this->db->fetchAll($sql, [$userId1, $userId2, $userId2, $userId1]);
        
        // Mark messages as read for current user (assuming userId1 is the current user)
        $this->markMessagesAsRead($userId2, $userId1);
        
        return [
            'messages' => array_reverse($messages), // Show oldest first
            'pagination' => $pagination,
            'other_user' => $user2
        ];
    }
    
    /**
     * Get user's conversations
     */
    public function getUserConversations($userId, $page = 1, $perPage = 10) {
        // Get total count
        $total = $this->db->fetchOne(
            "SELECT COUNT(DISTINCT CASE 
                WHEN user1_id = ? THEN user2_id 
                ELSE user1_id 
            END) as count 
            FROM conversations 
            WHERE user1_id = ? OR user2_id = ?",
            [$userId, $userId, $userId]
        )['count'];
        
        // Get pagination info
        $pagination = Utils::paginate($page, $perPage, $total);
        
        // Get conversations
        $sql = "SELECT DISTINCT 
                       CASE 
                           WHEN c.user1_id = ? THEN c.user2_id 
                           ELSE c.user1_id 
                       END as other_user_id,
                       u.full_name as other_user_name,
                       u.profile_image as other_user_image,
                       m.content as last_message,
                       m.sent_at as last_message_time,
                       m.is_read,
                       (SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND sender_id = other_user_id AND is_read = 0) as unread_count
                FROM conversations c 
                JOIN users u ON (
                    CASE 
                        WHEN c.user1_id = ? THEN c.user2_id 
                        ELSE c.user1_id 
                    END = u.id
                )
                LEFT JOIN messages m ON c.last_message_id = m.id
                WHERE c.user1_id = ? OR c.user2_id = ?
                ORDER BY c.last_activity DESC
                LIMIT {$pagination['offset']}, {$pagination['perPage']}";
        
        $conversations = $this->db->fetchAll($sql, [$userId, $userId, $userId, $userId, $userId]);
        
        // Format time ago for each conversation
        foreach ($conversations as &$conversation) {
            $conversation['last_message_time_ago'] = Utils::timeAgo($conversation['last_message_time']);
        }
        
        return [
            'conversations' => $conversations,
            'pagination' => $pagination
        ];
    }
    
    /**
     * Mark messages as read
     */
    public function markMessagesAsRead($senderId, $receiverId) {
        $this->db->update('messages', 
            ['is_read' => true], 
            'sender_id = ? AND receiver_id = ? AND is_read = 0', 
            [$senderId, $receiverId]
        );
        
        // Clear message notifications
        $this->db->delete('notifications', 
            'user_id = ? AND type = \'message\'', 
            [$receiverId]
        );
    }
    
    /**
     * Get unread message count for user
     */
    public function getUnreadCount($userId) {
        return $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM messages WHERE receiver_id = ? AND is_read = 0",
            [$userId]
        )['count'];
    }
    
    /**
     * Delete message
     */
    public function deleteMessage($messageId, $userId) {
        try {
            // Validate message ownership
            $message = $this->db->fetchOne(
                "SELECT * FROM messages WHERE id = ? AND (sender_id = ? OR receiver_id = ?)",
                [$messageId, $userId, $userId]
            );
            
            if (!$message) {
                throw new Exception("Message not found or access denied");
            }
            
            $this->db->delete('messages', 'id = ?', [$messageId]);
            
            return ['success' => true, 'message' => 'Message deleted successfully'];
            
        } catch (Exception $e) {
            error_log("Delete message error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Delete conversation
     */
    public function deleteConversation($otherUserId, $userId) {
        try {
            // Validate conversation exists
            $conversation = $this->getConversation($userId, $otherUserId);
            if (!$conversation) {
                throw new Exception("Conversation not found");
            }
            
            // Delete all messages in conversation
            $this->db->delete('messages', 
                '(sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)', 
                [$userId, $otherUserId, $otherUserId, $userId]
            );
            
            // Delete conversation
            $this->db->delete('conversations', 'id = ?', [$conversation['id']]);
            
            return ['success' => true, 'message' => 'Conversation deleted successfully'];
            
        } catch (Exception $e) {
            error_log("Delete conversation error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Search messages
     */
    public function searchMessages($userId, $searchTerm, $page = 1, $perPage = 10) {
        $where = [
            "(m.sender_id = ? OR m.receiver_id = ?)",
            "(m.content LIKE ? OR m.subject LIKE ?)"
        ];
        $params = [$userId, $userId, '%' . $searchTerm . '%', '%' . $searchTerm . '%'];
        
        // Get total count
        $countSql = "SELECT COUNT(DISTINCT m.id) as total
                     FROM messages m 
                     WHERE " . implode(' AND ', $where);
        $total = $this->db->fetchOne($countSql, $params)['total'];
        
        // Get pagination info
        $pagination = Utils::paginate($page, $perPage, $total);
        
        // Get messages
        $sql = "SELECT m.*, 
                       s.full_name as sender_name, s.profile_image as sender_image,
                       r.full_name as receiver_name
                FROM messages m 
                JOIN users s ON m.sender_id = s.id 
                JOIN users r ON m.receiver_id = r.id 
                WHERE " . implode(' AND ', $where) . "
                ORDER BY m.sent_at DESC
                LIMIT {$pagination['offset']}, {$pagination['perPage']}";
        
        $messages = $this->db->fetchAll($sql, $params);
        
        return [
            'messages' => $messages,
            'pagination' => $pagination
        ];
    }
    
    /**
     * Report message
     */
    public function reportMessage($messageId, $reporterId, $reason) {
        try {
            // Validate message exists
            $message = $this->db->fetchOne("SELECT * FROM messages WHERE id = ?", [$messageId]);
            if (!$message) {
                throw new Exception("Message not found");
            }
            
            // Create report (you'd need a reports table in a real implementation)
            $this->db->insert('message_reports', [
                'message_id' => $messageId,
                'reporter_id' => $reporterId,
                'reason' => Security::sanitizeInput($reason),
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Create admin notification
            $this->db->insert('notifications', [
                'user_id' => 1, // Admin user ID
                'type' => 'system',
                'title' => 'Message Reported',
                'message' => "Message ID $messageId has been reported for: $reason"
            ]);
            
            return ['success' => true, 'message' => 'Message reported successfully'];
            
        } catch (Exception $e) {
            error_log("Report message error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Block user
     */
    public function blockUser($blockerId, $blockedUserId) {
        try {
            // Validate users exist
            $blocker = $this->db->fetchOne("SELECT * FROM users WHERE id = ? AND is_active = 1", [$blockerId]);
            $blocked = $this->db->fetchOne("SELECT * FROM users WHERE id = ? AND is_active = 1", [$blockedUserId]);
            
            if (!$blocker || !$blocked) {
                throw new Exception("Invalid user");
            }
            
            // Can't block self
            if ($blockerId == $blockedUserId) {
                throw new Exception("Cannot block yourself");
            }
            
            // Check if already blocked
            if ($this->db->exists('blocked_users', 'blocker_id = ? AND blocked_user_id = ?', [$blockerId, $blockedUserId])) {
                throw new Exception("User already blocked");
            }
            
            // Add block
            $this->db->insert('blocked_users', [
                'blocker_id' => $blockerId,
                'blocked_user_id' => $blockedUserId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Delete existing conversation
            $this->deleteConversation($blockedUserId, $blockerId);
            
            return ['success' => true, 'message' => 'User blocked successfully'];
            
        } catch (Exception $e) {
            error_log("Block user error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Unblock user
     */
    public function unblockUser($blockerId, $blockedUserId) {
        try {
            $this->db->delete('blocked_users', 'blocker_id = ? AND blocked_user_id = ?', [$blockerId, $blockedUserId]);
            
            return ['success' => true, 'message' => 'User unblocked successfully'];
            
        } catch (Exception $e) {
            error_log("Unblock user error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get blocked users
     */
    public function getBlockedUsers($userId) {
        return $this->db->fetchAll(
            "SELECT u.id, u.full_name, u.profile_image, bu.created_at as blocked_at
             FROM blocked_users bu 
             JOIN users u ON bu.blocked_user_id = u.id 
             WHERE bu.blocker_id = ?
             ORDER BY bu.created_at DESC",
            [$userId]
        );
    }
    
    /**
     * Check if user is blocked
     */
    public function isBlocked($userId1, $userId2) {
        return $this->db->exists(
            'blocked_users', 
            '(blocker_id = ? AND blocked_user_id = ?) OR (blocker_id = ? AND blocked_user_id = ?)', 
            [$userId1, $userId2, $userId2, $userId1]
        );
    }
}