<?php
/**
 * Dashboard Applications Tab Content
 */

// Only allow access if included from dashboard
if (!defined('VOLUNTEER_CONNECT')) {
    exit('Direct access denied');
}
?>

<div class="card">
    <div class="card-header">
        <h3>My Applications</h3>
        <a href="opportunities.php" class="btn btn-primary">Find More Opportunities</a>
    </div>
    
    <?php if (empty($applications)): ?>
        <div style="text-align: center; padding: 2rem;">
            <i class="fas fa-paper-plane" style="font-size: 3rem; color: #6b7280; margin-bottom: 1rem;"></i>
            <h4>No Applications Yet</h4>
            <p style="color: #6b7280; margin-bottom: 1rem;">Start browsing opportunities and apply to positions that match your skills and interests.</p>
            <a href="opportunities.php" class="btn btn-primary">Browse Opportunities</a>
        </div>
    <?php else: ?>
        <div class="applications-list">
            <?php foreach ($applications as $application): ?>
                <div class="application-item">
                    <div class="application-header">
                        <div>
                            <h4>
                                <a href="opportunity-details.php?id=<?php echo $application['opportunity_id']; ?>">
                                    <?php echo htmlspecialchars($application['title']); ?>
                                </a>
                            </h4>
                            <p style="color: #6b7280;">
                                <i class="fas fa-building"></i> <?php echo htmlspecialchars($application['organization_name']); ?>
                            </p>
                        </div>
                        <span class="badge badge-<?php echo $application['status']; ?>">
                            <?php echo ucfirst($application['status']); ?>
                        </span>
                    </div>
                    
                    <?php if (!empty($application['description'])): ?>
                        <p style="margin: 1rem 0; color: #6b7280;">
                            <?php echo Utils::truncateText($application['description'], 150); ?>
                        </p>
                    <?php endif; ?>
                    
                    <div class="application-meta">
                        <span class="activity-time">
                            <i class="fas fa-clock"></i> Applied <?php echo Utils::timeAgo($application['applied_at']); ?>
                        </span>
                        <?php if (!empty($application['message'])): ?>
                            <span class="application-message">
                                <i class="fas fa-comment"></i> With message
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($application['status'] === 'pending'): ?>
                        <div class="application-actions" style="margin-top: 1rem;">
                            <button class="btn btn-outline btn-small" onclick="withdrawApplication(<?php echo $application['id']; ?>)">
                                <i class="fas fa-times"></i> Withdraw
                            </button>
                            <button class="btn btn-outline btn-small" onclick="viewApplication(<?php echo $application['id']; ?>)">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                        </div>
                    <?php elseif ($application['status'] === 'accepted'): ?>
                        <div class="application-actions" style="margin-top: 1rem;">
                            <button class="btn btn-primary btn-small" onclick="contactOrganization(<?php echo $application['opportunity_id']; ?>)">
                                <i class="fas fa-envelope"></i> Contact Organization
                            </button>
                            <button class="btn btn-secondary btn-small" onclick="markComplete(<?php echo $application['id']; ?>)">
                                <i class="fas fa-check"></i> Mark Complete
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (count($applications) >= 5): ?>
            <div style="text-align: center; margin-top: 2rem;">
                <a href="my-applications.php" class="btn btn-outline">View All Applications</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Application Statistics -->
<div class="card">
    <div class="card-header">
        <h4>Application Statistics</h4>
    </div>
    
    <div class="dashboard-stats">
        <div class="stat-card">
            <div class="stat-number"><?php echo count($applications); ?></div>
            <div class="stat-label">Total Applications</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">
                <?php 
                $accepted = count(array_filter($applications, fn($app) => $app['status'] === 'accepted'));
                echo $accepted; 
                ?>
            </div>
            <div class="stat-label">Accepted</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">
                <?php 
                $pending = count(array_filter($applications, fn($app) => $app['status'] === 'pending'));
                echo $pending; 
                ?>
            </div>
            <div class="stat-label">Pending</div>
        </div>
    </div>
</div>

<script>
function withdrawApplication(applicationId) {
    if (!confirm('Are you sure you want to withdraw this application?')) {
        return;
    }
    
    fetch('api/withdraw-application.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ id: applicationId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Application withdrawn successfully', 'success');
            location.reload();
        } else {
            showMessage(data.message || 'Failed to withdraw application', 'error');
        }
    })
    .catch(error => {
        showMessage('A network error occurred. Please try again.', 'error');
    });
}

function viewApplication(applicationId) {
    window.location.href = 'application-details.php?id=' + applicationId;
}

function contactOrganization(opportunityId) {
    window.location.href = 'messages.php?opportunity=' + opportunityId;
}

function markComplete(applicationId) {
    if (!confirm('Mark this volunteer work as complete?')) {
        return;
    }
    
    fetch('api/complete-application.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ id: applicationId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Application marked as complete', 'success');
            location.reload();
        } else {
            showMessage(data.message || 'Failed to update application', 'error');
        }
    })
    .catch(error => {
        showMessage('A network error occurred. Please try again.', 'error');
    });
}
</script>