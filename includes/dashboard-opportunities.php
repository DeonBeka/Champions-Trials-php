<?php
/**
 * Dashboard Opportunities Tab Content
 */

// Only allow access if included from dashboard
if (!defined('VOLUNTEER_CONNECT')) {
    exit('Direct access denied');
}
?>

<div class="card">
    <div class="card-header">
        <h3>My Opportunities</h3>
        <a href="create-opportunity.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create New Opportunity
        </a>
    </div>
    
    <?php if (empty($opportunities['opportunities'])): ?>
        <div style="text-align: center; padding: 2rem;">
            <i class="fas fa-bullhorn" style="font-size: 3rem; color: #6b7280; margin-bottom: 1rem;"></i>
            <h4>No Opportunities Posted</h4>
            <p style="color: #6b7280; margin-bottom: 1rem;">Create your first volunteer opportunity to start receiving applications from passionate volunteers.</p>
            <a href="create-opportunity.php" class="btn btn-primary">Create Opportunity</a>
        </div>
    <?php else: ?>
        <div class="opportunities-list">
            <?php foreach ($opportunities['opportunities'] as $opportunity): ?>
                <div class="opportunity-card compact">
                    <div class="opportunity-header">
                        <div>
                            <h4>
                                <a href="opportunity-details.php?id=<?php echo $opportunity['id']; ?>">
                                    <?php echo htmlspecialchars($opportunity['title']); ?>
                                </a>
                            </h4>
                            <p style="color: #6b7280; font-size: 0.875rem;">
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($opportunity['location']); ?>
                                <span style="margin-left: 1rem;">
                                    <i class="fas fa-clock"></i> <?php echo htmlspecialchars($opportunity['time_commitment']); ?>
                                </span>
                            </p>
                        </div>
                        <div style="text-align: right;">
                            <?php if ($opportunity['is_active']): ?>
                                <span class="badge badge-success">Active</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Inactive</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <p style="margin: 1rem 0; color: #6b7280; font-size: 0.875rem;">
                        <?php echo Utils::truncateText($opportunity['description'], 120); ?>
                    </p>
                    
                    <div class="opportunity-stats">
                        <span style="margin-right: 1rem;">
                            <i class="fas fa-users"></i> <?php echo $opportunity['applications_count']; ?> Applications
                        </span>
                        <span style="margin-right: 1rem;">
                            <i class="fas fa-check-circle" style="color: var(--success-color);"></i> <?php echo $opportunity['accepted_count']; ?> Accepted
                        </span>
                        <span>
                            <i class="fas fa-calendar"></i> Created <?php echo Utils::timeAgo($opportunity['created_at']); ?>
                        </span>
                    </div>
                    
                    <div class="opportunity-actions" style="margin-top: 1rem;">
                        <button class="btn btn-primary btn-small" onclick="viewApplications(<?php echo $opportunity['id']; ?>)">
                            <i class="fas fa-list"></i> View Applications
                        </button>
                        <button class="btn btn-outline btn-small" onclick="editOpportunity(<?php echo $opportunity['id']; ?>)">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-outline btn-small" onclick="shareOpportunity(<?php echo $opportunity['id']; ?>)">
                            <i class="fas fa-share"></i> Share
                        </button>
                        <?php if ($opportunity['is_active']): ?>
                            <button class="btn btn-outline btn-small" onclick="toggleOpportunityStatus(<?php echo $opportunity['id']; ?>, false)">
                                <i class="fas fa-pause"></i> Deactivate
                            </button>
                        <?php else: ?>
                            <button class="btn btn-primary btn-small" onclick="toggleOpportunityStatus(<?php echo $opportunity['id']; ?>, true)">
                                <i class="fas fa-play"></i> Activate
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if ($opportunities['pagination']['totalPages'] > 1): ?>
            <div style="text-align: center; margin-top: 2rem;">
                <a href="my-opportunities.php" class="btn btn-outline">View All Opportunities</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Opportunity Statistics -->
<div class="card">
    <div class="card-header">
        <h4>Opportunity Statistics</h4>
    </div>
    
    <div class="dashboard-stats">
        <div class="stat-card">
            <div class="stat-number"><?php echo $opportunities['pagination']['total']; ?></div>
            <div class="stat-label">Total Opportunities</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">
                <?php 
                $totalApplications = array_sum(array_column($opportunities['opportunities'], 'applications_count'));
                echo $totalApplications; 
                ?>
            </div>
            <div class="stat-label">Total Applications</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">
                <?php 
                $totalAccepted = array_sum(array_column($opportunities['opportunities'], 'accepted_count'));
                echo $totalAccepted; 
                ?>
            </div>
            <div class="stat-label">Volunteers Connected</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">
                <?php 
                $activeCount = count(array_filter($opportunities['opportunities'], fn($opp) => $opp['is_active']));
                echo $activeCount; 
                ?>
            </div>
            <div class="stat-label">Active Opportunities</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card">
    <div class="card-header">
        <h4>Quick Actions</h4>
    </div>
    
    <div class="quick-actions-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
        <button class="btn btn-primary" onclick="window.location.href='create-opportunity.php'">
            <i class="fas fa-plus"></i> Create Opportunity
        </button>
        <button class="btn btn-secondary" onclick="window.location.href='my-opportunities.php'">
            <i class="fas fa-list"></i> Manage All
        </button>
        <button class="btn btn-outline" onclick="exportApplications()">
            <i class="fas fa-download"></i> Export Data
        </button>
        <button class="btn btn-outline" onclick="showInsights()">
            <i class="fas fa-chart-bar"></i> View Insights
        </button>
    </div>
</div>

<script>
function viewApplications(opportunityId) {
    window.location.href = 'opportunity-applications.php?id=' + opportunityId;
}

function editOpportunity(opportunityId) {
    window.location.href = 'edit-opportunity.php?id=' + opportunityId;
}

function shareOpportunity(opportunityId) {
    const url = window.location.origin + '/opportunity-details.php?id=' + opportunityId;
    
    if (navigator.share) {
        navigator.share({
            title: 'Volunteer Opportunity',
            text: 'Check out this volunteer opportunity!',
            url: url
        });
    } else {
        navigator.clipboard.writeText(url);
        showMessage('Opportunity link copied to clipboard!', 'success');
    }
}

function toggleOpportunityStatus(opportunityId, activate) {
    const action = activate ? 'activate' : 'deactivate';
    const confirmText = `Are you sure you want to ${action} this opportunity?`;
    
    if (!confirm(confirmText)) {
        return;
    }
    
    fetch('api/toggle-opportunity.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ id: opportunityId, activate: activate })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(`Opportunity ${action}d successfully`, 'success');
            location.reload();
        } else {
            showMessage(data.message || `Failed to ${action} opportunity`, 'error');
        }
    })
    .catch(error => {
        showMessage('A network error occurred. Please try again.', 'error');
    });
}

function exportApplications() {
    window.location.href = 'api/export-applications.php';
}

function showInsights() {
    showModal('insightsModal');
}
</script>