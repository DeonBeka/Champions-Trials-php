<?php
/**
 * Dashboard Profile Tab Content
 */

// Only allow access if included from dashboard
if (!defined('VOLUNTEER_CONNECT')) {
    exit('Direct access denied');
}
?>

<div class="card">
    <div class="card-header">
        <h3>Profile Information</h3>
        <button class="btn btn-outline" onclick="enableProfileEditing()" id="editProfileBtn">
            <i class="fas fa-edit"></i> Edit Profile
        </button>
        <div id="profileActions" style="display: none;">
            <button class="btn btn-primary" onclick="saveProfile()" id="saveProfileBtn">
                <i class="fas fa-save"></i> Save Changes
            </button>
            <button class="btn btn-outline" onclick="cancelProfileEdit()">
                <i class="fas fa-times"></i> Cancel
            </button>
        </div>
    </div>
    
    <form id="profileForm" method="POST" action="dashboard.php">
        <input type="hidden" name="action" value="update_profile">
        <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">
        
        <div id="profileContent">
            <div style="display: flex; align-items: start; gap: 2rem;">
                <div class="avatar" style="width: 120px; height: 120px; font-size: 3rem;">
                    <?php echo strtoupper(substr($fullProfile['full_name'], 0, 1)); ?>
                </div>
                <div style="flex: 1;">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-input profile-field" name="full_name" 
                               value="<?php echo htmlspecialchars($fullProfile['full_name']); ?>" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-input profile-field" name="email" 
                               value="<?php echo htmlspecialchars($fullProfile['email']); ?>" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-input profile-field" name="location" 
                               value="<?php echo htmlspecialchars($fullProfile['location']); ?>" disabled>
                    </div>
                    
                    <?php if ($fullProfile['user_type'] === 'organization'): ?>
                        <div class="form-group">
                            <label class="form-label">Organization Name</label>
                            <input type="text" class="form-input profile-field" name="org_name" 
                                   value="<?php echo htmlspecialchars($fullProfile['organization_data']['org_name'] ?? ''); ?>" disabled>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Organization Type</label>
                            <select class="form-select profile-field" name="org_type" disabled>
                                <option value="">Select type</option>
                                <option value="nonprofit" <?php echo ($fullProfile['organization_data']['org_type'] ?? '') === 'nonprofit' ? 'selected' : ''; ?>>Non-Profit</option>
                                <option value="charity" <?php echo ($fullProfile['organization_data']['org_type'] ?? '') === 'charity' ? 'selected' : ''; ?>>Charity</option>
                                <option value="school" <?php echo ($fullProfile['organization_data']['org_type'] ?? '') === 'school' ? 'selected' : ''; ?>>Educational Institution</option>
                                <option value="hospital" <?php echo ($fullProfile['organization_data']['org_type'] ?? '') === 'hospital' ? 'selected' : ''; ?>>Healthcare</option>
                                <option value="government" <?php echo ($fullProfile['organization_data']['org_type'] ?? '') === 'government' ? 'selected' : ''; ?>>Government</option>
                                <option value="community" <?php echo ($fullProfile['organization_data']['org_type'] ?? '') === 'community' ? 'selected' : ''; ?>>Community Group</option>
                            </select>
                        </div>
                    <?php else: ?>
                        <div class="form-group">
                            <label class="form-label">Availability</label>
                            <select class="form-select profile-field" name="availability" disabled>
                                <option value="">Select availability</option>
                                <option value="weekends" <?php echo ($fullProfile['volunteer_data']['availability'] ?? '') === 'weekends' ? 'selected' : ''; ?>>Weekends only</option>
                                <option value="evenings" <?php echo ($fullProfile['volunteer_data']['availability'] ?? '') === 'evenings' ? 'selected' : ''; ?>>Evenings</option>
                                <option value="weekdays" <?php echo ($fullProfile['volunteer_data']['availability'] ?? '') === 'weekdays' ? 'selected' : ''; ?>>Weekdays</option>
                                <option value="flexible" <?php echo ($fullProfile['volunteer_data']['availability'] ?? '') === 'flexible' ? 'selected' : ''; ?>>Flexible</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Hours per week</label>
                            <input type="number" class="form-input profile-field" name="hours_per_week" 
                                   value="<?php echo htmlspecialchars($fullProfile['volunteer_data']['hours_per_week'] ?? ''); ?>" disabled>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label class="form-label">Bio/About</label>
                        <textarea class="form-textarea profile-field" name="bio" rows="4" disabled><?php echo htmlspecialchars($fullProfile['bio']); ?></textarea>
                    </div>
                    
                    <?php if (!empty($fullProfile['skills'])): ?>
                        <div class="form-group">
                            <label class="form-label">Skills</label>
                            <div class="tags-container">
                                <?php foreach ($fullProfile['skills'] as $skill): ?>
                                    <span class="tag"><?php echo htmlspecialchars($skill['name']); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($fullProfile['interests'])): ?>
                        <div class="form-group">
                            <label class="form-label">Interests</label>
                            <div class="tags-container">
                                <?php foreach ($fullProfile['interests'] as $interest): ?>
                                    <span class="tag"><?php echo htmlspecialchars($interest['name']); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </form>
</div>