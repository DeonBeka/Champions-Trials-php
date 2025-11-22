// Volunteer Connect - Complete JavaScript Functionality

// Global State Management
let currentUser = null;
let selectedUserType = '';
let applications = [];
let conversations = [];
let opportunities = [];
let volunteers = [];
let messages = [];

// Initialize the application
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
    setupEventListeners();
    loadSampleData();
});

function initializeApp() {
    // Check if user is logged in (from localStorage)
    const savedUser = localStorage.getItem('volunteerConnectUser');
    if (savedUser) {
        currentUser = JSON.parse(savedUser);
        updateUIForLoggedInUser();
    }
    
    // Show home page by default
    showPage('home');
}

function setupEventListeners() {
    // Form submissions
    document.getElementById('signupForm').addEventListener('submit', handleSignup);
    document.getElementById('loginForm').addEventListener('submit', handleLogin);
    
    // Search functionality
    document.getElementById('opportunitySearch').addEventListener('input', debounce(searchOpportunities, 300));
    document.getElementById('volunteerSearch').addEventListener('input', debounce(searchVolunteers, 300));
    
    // Modal close on background click
    document.getElementById('featureModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
}

// Page Navigation
function showPage(pageName) {
    // Hide all pages
    document.querySelectorAll('.page').forEach(page => {
        page.classList.remove('active');
    });
    
    // Show selected page
    const targetPage = document.getElementById(pageName + 'Page');
    if (targetPage) {
        targetPage.classList.add('active');
    }
    
    // Update navigation active state
    updateNavigation(pageName);
    
    // Load page-specific content
    loadPageContent(pageName);
}

function updateNavigation(activePage) {
    document.querySelectorAll('.nav-links a').forEach(link => {
        link.classList.remove('active');
    });
    
    const activeLink = document.querySelector(`.nav-links a[href="#${activePage}"]`);
    if (activeLink) {
        activeLink.classList.add('active');
    }
}

function loadPageContent(pageName) {
    switch(pageName) {
        case 'opportunities':
            loadOpportunities();
            break;
        case 'volunteers':
            loadVolunteers();
            break;
        case 'dashboard':
            loadDashboard();
            break;
        case 'messages':
            loadMessages();
            break;
    }
}

// Authentication Functions
function selectUserType(type) {
    selectedUserType = type;
    
    // Update UI
    document.querySelectorAll('.user-type-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    event.currentTarget.classList.add('selected');
    
    // Show/hide specific fields
    document.getElementById('volunteerFields').style.display = 
        type === 'volunteer' ? 'block' : 'none';
    document.getElementById('organizationFields').style.display = 
        type === 'organization' ? 'block' : 'none';
}

function handleSignup(e) {
    e.preventDefault();
    
    if (!selectedUserType) {
        showMessage('signupMessage', 'Please select whether you are a volunteer or organization', 'error');
        return;
    }
    
    // Collect form data
    const formData = {
        id: Date.now().toString(),
        userType: selectedUserType,
        fullName: document.getElementById('fullName').value,
        email: document.getElementById('email').value,
        password: document.getElementById('password').value,
        location: document.getElementById('location').value,
        bio: document.getElementById('bio').value,
        createdAt: new Date().toISOString()
    };
    
    // Add user type specific data
    if (selectedUserType === 'volunteer') {
        formData.skills = getSelectedTags('volunteerFields');
        formData.interests = getSelectedTags('volunteerFields', 1);
        formData.availability = document.getElementById('availability').value;
        formData.hoursPerWeek = document.getElementById('hoursPerWeek').value;
    } else {
        formData.orgName = document.getElementById('orgName').value;
        formData.orgType = document.getElementById('orgType').value;
        formData.missionStatement = document.getElementById('missionStatement').value;
        formData.website = document.getElementById('website').value;
    }
    
    // Save user
    currentUser = formData;
    localStorage.setItem('volunteerConnectUser', JSON.stringify(currentUser));
    
    // Update UI
    showMessage('signupMessage', 'Account created successfully!', 'success');
    setTimeout(() => {
        updateUIForLoggedInUser();
        showPage('dashboard');
    }, 1500);
}

function handleLogin(e) {
    e.preventDefault();
    
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;
    
    // For demo purposes, accept any login with email and password
    // In real app, this would validate against backend
    if (email && password) {
        currentUser = {
            id: Date.now().toString(),
            userType: email.includes('org') ? 'organization' : 'volunteer',
            fullName: email.split('@')[0],
            email: email,
            location: 'New York, USA',
            bio: 'Demo user for Volunteer Connect'
        };
        
        localStorage.setItem('volunteerConnectUser', JSON.stringify(currentUser));
        updateUIForLoggedInUser();
        showPage('dashboard');
    } else {
        showMessage('loginMessage', 'Please enter valid email and password', 'error');
    }
}

function logout() {
    currentUser = null;
    localStorage.removeItem('volunteerConnectUser');
    updateUIForLoggedOutUser();
    showPage('home');
}

function updateUIForLoggedInUser() {
    if (!currentUser) return;
    
    // Hide login/signup buttons, show logout
    document.getElementById('loginBtn').style.display = 'none';
    document.getElementById('signupBtn').style.display = 'none';
    document.getElementById('logoutBtn').style.display = 'block';
    
    // Show navigation links
    document.getElementById('navLinks').style.display = 'flex';
}

function updateUIForLoggedOutUser() {
    // Show login/signup buttons, hide logout
    document.getElementById('loginBtn').style.display = 'inline-block';
    document.getElementById('signupBtn').style.display = 'inline-block';
    document.getElementById('logoutBtn').style.display = 'none';
    
    // Hide some navigation for non-logged in users
    document.getElementById('navLinks').style.display = 'none';
}

// Tag Selection Functions
function toggleTag(element) {
    element.classList.toggle('selected');
}

function getSelectedTags(containerId, tagSet = 0) {
    const container = document.getElementById(containerId);
    const tagContainers = container.querySelectorAll('.tags-container');
    if (tagContainers[tagSet]) {
        return Array.from(tagContainers[tagSet].querySelectorAll('.tag.selected'))
            .map(tag => tag.textContent);
    }
    return [];
}

// Opportunities Functions
function loadOpportunities() {
    const container = document.querySelector('.opportunities-grid');
    if (!container) return;
    
    const html = opportunities.map(opp => `
        <div class="opportunity-card">
            <div class="opportunity-header">
                <div>
                    <h3 class="opportunity-title">${opp.title}</h3>
                    <p>${opp.organization}</p>
                </div>
                <span class="badge">${opp.category}</span>
            </div>
            <p>${opp.description}</p>
            <div class="opportunity-meta">
                <span><i class="fas fa-map-marker-alt"></i> ${opp.location}</span>
                <span><i class="fas fa-clock"></i> ${opp.timeCommitment}</span>
                <span><i class="fas fa-calendar"></i> ${opp.duration}</span>
            </div>
            <div style="margin-top: 1rem;">
                <button class="btn btn-primary" onclick="applyForOpportunity('${opp.id}')">
                    Apply Now
                </button>
                <button class="btn btn-outline" onclick="viewOpportunityDetails('${opp.id}')">
                    Learn More
                </button>
            </div>
        </div>
    `).join('');
    
    container.innerHTML = html;
}

function searchOpportunities() {
    const searchTerm = document.getElementById('opportunitySearch').value.toLowerCase();
    const location = document.getElementById('locationFilter').value;
    const category = document.getElementById('categoryFilter').value;
    
    let filtered = opportunities;
    
    if (searchTerm) {
        filtered = filtered.filter(opp => 
            opp.title.toLowerCase().includes(searchTerm) ||
            opp.description.toLowerCase().includes(searchTerm) ||
            opp.organization.toLowerCase().includes(searchTerm)
        );
    }
    
    if (category) {
        filtered = filtered.filter(opp => opp.category === category);
    }
    
    if (location) {
        filtered = filtered.filter(opp => opp.location.includes(location));
    }
    
    // Update display
    const container = document.querySelector('.opportunities-grid');
    const html = filtered.map(opp => `
        <div class="opportunity-card">
            <div class="opportunity-header">
                <div>
                    <h3 class="opportunity-title">${opp.title}</h3>
                    <p>${opp.organization}</p>
                </div>
                <span class="badge">${opp.category}</span>
            </div>
            <p>${opp.description}</p>
            <div class="opportunity-meta">
                <span><i class="fas fa-map-marker-alt"></i> ${opp.location}</span>
                <span><i class="fas fa-clock"></i> ${opp.timeCommitment}</span>
                <span><i class="fas fa-calendar"></i> ${opp.duration}</span>
            </div>
            <div style="margin-top: 1rem;">
                <button class="btn btn-primary" onclick="applyForOpportunity('${opp.id}')">
                    Apply Now
                </button>
                <button class="btn btn-outline" onclick="viewOpportunityDetails('${opp.id}')">
                    Learn More
                </button>
            </div>
        </div>
    `).join('');
    
    container.innerHTML = html || '<p>No opportunities found matching your criteria.</p>';
}

function applyForOpportunity(opportunityId) {
    if (!currentUser) {
        showMessage('loginMessage', 'Please login to apply for opportunities', 'error');
        showPage('login');
        return;
    }
    
    const opportunity = opportunities.find(opp => opp.id === opportunityId);
    if (opportunity) {
        const application = {
            id: Date.now().toString(),
            userId: currentUser.id,
            opportunityId: opportunityId,
            status: 'pending',
            appliedAt: new Date().toISOString()
        };
        
        applications.push(application);
        showMessage('dashboardMessage', 'Application submitted successfully!', 'success');
        
        // Add to dashboard
        loadDashboard();
    }
}

function viewOpportunityDetails(opportunityId) {
    const opportunity = opportunities.find(opp => opp.id === opportunityId);
    if (opportunity) {
        showOpportunityModal(opportunity);
    }
}

// Volunteers Functions
function loadVolunteers() {
    const container = document.querySelector('.volunteers-grid');
    if (!container) return;
    
    const html = volunteers.map(volunteer => `
        <div class="card">
            <div class="card-header" style="display: flex; align-items: center; gap: 1rem;">
                <div class="avatar">${volunteer.fullName.charAt(0)}</div>
                <div>
                    <h3>${volunteer.fullName}</h3>
                    <p style="color: #6b7280;">${volunteer.location}</p>
                </div>
            </div>
            <p>${volunteer.bio}</p>
            <div class="tags-container" style="margin: 1rem 0;">
                ${volunteer.skills.map(skill => `<span class="tag">${skill}</span>`).join('')}
            </div>
            <div class="opportunity-meta">
                <span><i class="fas fa-clock"></i> ${volunteer.availability}</span>
                <span><i class="fas fa-hourglass-half"></i> ${volunteer.hoursPerWeek} hrs/week</span>
            </div>
            <div style="margin-top: 1rem;">
                <button class="btn btn-primary" onclick="contactVolunteer('${volunteer.id}')">
                    Contact
                </button>
                <button class="btn btn-outline" onclick="viewVolunteerProfile('${volunteer.id}')">
                    View Profile
                </button>
            </div>
        </div>
    `).join('');
    
    container.innerHTML = html;
}

function searchVolunteers() {
    const searchTerm = document.getElementById('volunteerSearch').value.toLowerCase();
    const skill = document.getElementById('skillFilter').value;
    const availability = document.getElementById('availabilityFilter').value;
    
    let filtered = volunteers;
    
    if (searchTerm) {
        filtered = filtered.filter(vol => 
            vol.fullName.toLowerCase().includes(searchTerm) ||
            vol.bio.toLowerCase().includes(searchTerm) ||
            vol.skills.some(s => s.toLowerCase().includes(searchTerm))
        );
    }
    
    if (skill) {
        filtered = filtered.filter(vol => vol.skills.includes(skill));
    }
    
    if (availability) {
        filtered = filtered.filter(vol => vol.availability === availability);
    }
    
    // Update display
    const container = document.querySelector('.volunteers-grid');
    const html = filtered.map(volunteer => `
        <div class="card">
            <div class="card-header" style="display: flex; align-items: center; gap: 1rem;">
                <div class="avatar">${volunteer.fullName.charAt(0)}</div>
                <div>
                    <h3>${volunteer.fullName}</h3>
                    <p style="color: #6b7280;">${volunteer.location}</p>
                </div>
            </div>
            <p>${volunteer.bio}</p>
            <div class="tags-container" style="margin: 1rem 0;">
                ${volunteer.skills.map(skill => `<span class="tag">${skill}</span>`).join('')}
            </div>
            <div class="opportunity-meta">
                <span><i class="fas fa-clock"></i> ${volunteer.availability}</span>
                <span><i class="fas fa-hourglass-half"></i> ${volunteer.hoursPerWeek} hrs/week</span>
            </div>
            <div style="margin-top: 1rem;">
                <button class="btn btn-primary" onclick="contactVolunteer('${volunteer.id}')">
                    Contact
                </button>
                <button class="btn btn-outline" onclick="viewVolunteerProfile('${volunteer.id}')">
                    View Profile
                </button>
            </div>
        </div>
    `).join('');
    
    container.innerHTML = html || '<p>No volunteers found matching your criteria.</p>';
}

function contactVolunteer(volunteerId) {
    if (!currentUser) {
        showMessage('loginMessage', 'Please login to contact volunteers', 'error');
        showPage('login');
        return;
    }
    
    const volunteer = volunteers.find(v => v.id === volunteerId);
    if (volunteer) {
        startConversation(volunteer);
    }
}

function viewVolunteerProfile(volunteerId) {
    const volunteer = volunteers.find(v => v.id === volunteerId);
    if (volunteer) {
        showVolunteerModal(volunteer);
    }
}

// Dashboard Functions
function loadDashboard() {
    if (!currentUser) return;
    
    // Update stats
    const userApplications = applications.filter(app => app.userId === currentUser.id);
    document.getElementById('totalApplications').textContent = userApplications.length;
    
    // Profile content
    loadProfileContent();
    
    // Applications list
    loadApplicationsList();
    
    // Calendar
    loadCalendar();
}

function loadProfileContent() {
    const container = document.getElementById('profileContent');
    if (!container || !currentUser) return;
    
    const html = `
        <div style="display: flex; align-items: start; gap: 2rem;">
            <div class="avatar" style="width: 120px; height: 120px; font-size: 3rem;">
                ${currentUser.fullName.charAt(0)}
            </div>
            <div style="flex: 1;">
                <h2>${currentUser.fullName}</h2>
                <p style="color: #6b7280; margin-bottom: 1rem;">
                    <i class="fas fa-envelope"></i> ${currentUser.email}<br>
                    <i class="fas fa-map-marker-alt"></i> ${currentUser.location}<br>
                    ${currentUser.userType === 'organization' ? `<i class="fas fa-building"></i> ${currentUser.orgName || 'Organization'}` : ''}
                </p>
                <p>${currentUser.bio}</p>
                ${currentUser.skills ? `
                    <div style="margin-top: 1rem;">
                        <strong>Skills:</strong>
                        <div class="tags-container">
                            ${currentUser.skills.map(skill => `<span class="tag">${skill}</span>`).join('')}
                        </div>
                    </div>
                ` : ''}
                ${currentUser.interests ? `
                    <div style="margin-top: 1rem;">
                        <strong>Interests:</strong>
                        <div class="tags-container">
                            ${currentUser.interests.map(interest => `<span class="tag">${interest}</span>`).join('')}
                        </div>
                    </div>
                ` : ''}
            </div>
        </div>
    `;
    
    container.innerHTML = html;
}

function loadApplicationsList() {
    const container = document.getElementById('applicationsList');
    if (!container) return;
    
    const userApplications = applications.filter(app => app.userId === currentUser.id);
    
    if (userApplications.length === 0) {
        container.innerHTML = '<p>No applications yet. Start browsing opportunities!</p>';
        return;
    }
    
    const html = userApplications.map(app => {
        const opportunity = opportunities.find(opp => opp.id === app.opportunityId);
        return `
            <div class="opportunity-card">
                <div class="opportunity-header">
                    <div>
                        <h4>${opportunity?.title || 'Unknown Opportunity'}</h4>
                        <p>${opportunity?.organization || 'Unknown Organization'}</p>
                    </div>
                    <span class="badge badge-${app.status}">${app.status}</span>
                </div>
                <p style="color: #6b7280;">Applied: ${new Date(app.appliedAt).toLocaleDateString()}</p>
            </div>
        `;
    }).join('');
    
    container.innerHTML = html;
}

function loadCalendar() {
    const container = document.getElementById('calendarGrid');
    if (!container) return;
    
    // Generate calendar for current month
    const now = new Date();
    const year = now.getFullYear();
    const month = now.getMonth();
    
    document.getElementById('calendarMonth').textContent = 
        now.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
    
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    
    let html = '';
    
    // Day headers
    const dayHeaders = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    dayHeaders.forEach(day => {
        html += `<div class="calendar-day" style="font-weight: bold;">${day}</div>`;
    });
    
    // Empty cells for days before month starts
    for (let i = 0; i < firstDay; i++) {
        html += '<div class="calendar-day"></div>';
    }
    
    // Days of the month
    for (let day = 1; day <= daysInMonth; day++) {
        html += `<div class="calendar-day" onclick="selectDate(${year}, ${month}, ${day})">${day}</div>`;
    }
    
    container.innerHTML = html;
}

function switchTab(tabName) {
    // Update tab buttons
    document.querySelectorAll('.tab').forEach(tab => {
        tab.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Show corresponding content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    document.getElementById(tabName + 'Tab').classList.add('active');
}

// Messages Functions
function loadMessages() {
    if (!currentUser) return;
    
    // Load conversations
    const conversationsContainer = document.getElementById('conversationsList');
    const html = conversations.map(conv => `
        <div class="conversation-item" onclick="loadConversation('${conv.id}')" style="padding: 1rem; border-bottom: 1px solid var(--border-color); cursor: pointer;">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div class="avatar">${conv.otherUser.charAt(0)}</div>
                <div style="flex: 1;">
                    <h4>${conv.otherUser}</h4>
                    <p style="color: #6b7280; font-size: 0.875rem;">${conv.lastMessage}</p>
                </div>
                <span style="color: #6b7280; font-size: 0.75rem;">${conv.timestamp}</span>
            </div>
        </div>
    `).join('');
    
    conversationsContainer.innerHTML = html || '<p>No conversations yet.</p>';
}

function loadConversation(conversationId) {
    const conversation = conversations.find(c => c.id === conversationId);
    if (!conversation) return;
    
    document.getElementById('conversationTitle').textContent = conversation.otherUser;
    
    const messagesContainer = document.getElementById('messagesContainer');
    const html = conversation.messages.map(msg => `
        <div class="message ${msg.sender === currentUser.id ? 'message-sent' : 'message-received'}" style="margin-bottom: 1rem;">
            <div style="background: ${msg.sender === currentUser.id ? 'var(--primary-color)' : 'var(--light-color)'}; 
                        color: ${msg.sender === currentUser.id ? 'white' : 'var(--dark-color)'}; 
                        padding: 1rem; 
                        border-radius: var(--radius); 
                        max-width: 70%;">
                <p>${msg.content}</p>
                <small style="opacity: 0.7;">${new Date(msg.timestamp).toLocaleTimeString()}</small>
            </div>
        </div>
    `).join('');
    
    messagesContainer.innerHTML = html;
}

function startNewConversation() {
    showNewConversationModal();
}

function startConversation(user) {
    // Create or find conversation with this user
    let conversation = conversations.find(c => c.otherUserId === user.id);
    
    if (!conversation) {
        conversation = {
            id: Date.now().toString(),
            otherUserId: user.id,
            otherUser: user.fullName,
            messages: [],
            lastMessage: 'Start a conversation...',
            timestamp: 'Just now'
        };
        conversations.push(conversation);
    }
    
    showPage('messages');
    loadConversation(conversation.id);
}

function sendMessage() {
    const input = document.getElementById('messageInput');
    const content = input.value.trim();
    
    if (!content) return;
    
    // Add message to current conversation
    const message = {
        sender: currentUser.id,
        content: content,
        timestamp: new Date().toISOString()
    };
    
    // This would be connected to the current conversation in a real app
    input.value = '';
    
    // Reload messages
    loadMessages();
}

// Modal Functions
function showModal(title, content) {
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalBody').innerHTML = content;
    document.getElementById('featureModal').classList.add('active');
}

function closeModal() {
    document.getElementById('featureModal').classList.remove('active');
}

function showOpportunityModal(opportunity) {
    const content = `
        <div class="opportunity-details">
            <h3>${opportunity.title}</h3>
            <p><strong>Organization:</strong> ${opportunity.organization}</p>
            <p><strong>Location:</strong> ${opportunity.location}</p>
            <p><strong>Category:</strong> ${opportunity.category}</p>
            <p><strong>Time Commitment:</strong> ${opportunity.timeCommitment}</p>
            <p><strong>Duration:</strong> ${opportunity.duration}</p>
            <hr style="margin: 1rem 0;">
            <p>${opportunity.description}</p>
            <div style="margin-top: 2rem;">
                <button class="btn btn-primary" onclick="applyForOpportunity('${opportunity.id}'); closeModal();">Apply Now</button>
                <button class="btn btn-outline" onclick="closeModal()">Close</button>
            </div>
        </div>
    `;
    showModal('Opportunity Details', content);
}

function showVolunteerModal(volunteer) {
    const content = `
        <div class="volunteer-details">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                <div class="avatar">${volunteer.fullName.charAt(0)}</div>
                <div>
                    <h3>${volunteer.fullName}</h3>
                    <p style="color: #6b7280;">${volunteer.location}</p>
                </div>
            </div>
            <p>${volunteer.bio}</p>
            <div style="margin: 1rem 0;">
                <strong>Skills:</strong>
                <div class="tags-container">
                    ${volunteer.skills.map(skill => `<span class="tag">${skill}</span>`).join('')}
                </div>
            </div>
            <div style="margin: 1rem 0;">
                <strong>Availability:</strong> ${volunteer.availability} (${volunteer.hoursPerWeek} hours/week)
            </div>
            <div style="margin-top: 2rem;">
                <button class="btn btn-primary" onclick="contactVolunteer('${volunteer.id}'); closeModal();">Contact Volunteer</button>
                <button class="btn btn-outline" onclick="closeModal()">Close</button>
            </div>
        </div>
    `;
    showModal('Volunteer Profile', content);
}

function showNewConversationModal() {
    const content = `
        <div class="new-conversation">
            <div class="form-group">
                <label class="form-label">Search for user:</label>
                <input type="text" class="form-input" id="newConversationSearch" placeholder="Type name or email...">
            </div>
            <div class="form-group">
                <label class="form-label">Message:</label>
                <textarea class="form-textarea" id="newConversationMessage" placeholder="Type your message..."></textarea>
            </div>
            <div style="display: flex; gap: 1rem;">
                <button class="btn btn-primary" onclick="createNewConversation()">Send Message</button>
                <button class="btn btn-outline" onclick="closeModal()">Cancel</button>
            </div>
        </div>
    `;
    showModal('New Conversation', content);
}

// Utility Functions
function showMessage(containerId, message, type = 'info') {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    container.textContent = message;
    container.className = `message message-${type}`;
    container.style.display = 'block';
    
    setTimeout(() => {
        container.style.display = 'none';
    }, 5000);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Load Sample Data
function loadSampleData() {
    // Sample opportunities
    opportunities = [
        {
            id: '1',
            title: 'Teaching Assistant for After-School Program',
            organization: 'Community Education Center',
            description: 'Help elementary school students with homework and lead educational activities in our after-school program. Looking for patient volunteers who love working with children.',
            location: 'New York, NY',
            category: 'Education',
            timeCommitment: '3 hours/week',
            duration: 'Ongoing'
        },
        {
            id: '2',
            title: 'Food Bank Volunteer',
            organization: 'City Food Bank',
            description: 'Sort and distribute food to families in need. Help us fight hunger in our community by assisting with food packing and distribution.',
            location: 'Los Angeles, CA',
            category: 'Community Service',
            timeCommitment: '4 hours/week',
            duration: '3 months'
        },
        {
            id: '3',
            title: 'Environmental Cleanup',
            organization: 'Green Earth Initiative',
            description: 'Join our weekend cleanup events to help preserve local parks and waterways. Great for outdoor enthusiasts who want to make a difference.',
            location: 'Chicago, IL',
            category: 'Environment',
            timeCommitment: 'Weekend events',
            duration: 'Flexible'
        },
        {
            id: '4',
            title: 'Animal Care Volunteer',
            organization: 'Happy Paws Shelter',
            description: 'Help care for rescued animals including feeding, walking dogs, and socializing cats. Experience with animals preferred but not required.',
            location: 'Austin, TX',
            category: 'Animal Welfare',
            timeCommitment: '2-3 shifts/week',
            duration: 'Ongoing'
        },
        {
            id: '5',
            title: 'Senior Companion',
            organization: 'Golden Years Senior Center',
            description: 'Spend time with elderly residents, play games, read, and provide companionship. Bring joy to seniors who may be lonely.',
            location: 'Phoenix, AZ',
            category: 'Senior Care',
            timeCommitment: '2 hours/week',
            duration: 'Ongoing'
        }
    ];
    
    // Sample volunteers
    volunteers = [
        {
            id: '1',
            fullName: 'Sarah Johnson',
            email: 'sarah@example.com',
            location: 'New York, NY',
            bio: 'Passionate educator with 5 years of teaching experience. Love working with children and making learning fun!',
            skills: ['Teaching', 'Mentoring', 'Arts & Crafts'],
            interests: ['Education', 'Youth Development'],
            availability: 'Weekends',
            hoursPerWeek: '5'
        },
        {
            id: '2',
            fullName: 'Michael Chen',
            email: 'michael@example.com',
            location: 'Los Angeles, CA',
            bio: 'Tech professional looking to give back to the community. Skilled in web development and digital marketing.',
            skills: ['IT/Tech', 'Web Development', 'Marketing'],
            interests: ['Education', 'Nonprofit Support'],
            availability: 'Evenings',
            hoursPerWeek: '3'
        },
        {
            id: '3',
            fullName: 'Emily Rodriguez',
            email: 'emily@example.com',
            location: 'Chicago, IL',
            bio: 'Environmental science graduate passionate about conservation and sustainability. Love organizing community events.',
            skills: ['Environmental', 'Event Planning', 'Public Speaking'],
            interests: ['Environment', 'Community Service'],
            availability: 'Flexible',
            hoursPerWeek: '8'
        }
    ];
    
    // Sample conversations
    conversations = [
        {
            id: '1',
            otherUserId: '1',
            otherUser: 'Sarah Johnson',
            messages: [
                {
                    sender: '1',
                    content: 'Hi! I saw your application for the teaching assistant position.',
                    timestamp: '2024-12-01T10:00:00Z'
                },
                {
                    sender: currentUser?.id || 'current',
                    content: 'Hello! Yes, I\'m very interested in the opportunity.',
                    timestamp: '2024-12-01T10:30:00Z'
                }
            ],
            lastMessage: 'Hello! Yes, I\'m very interested in the opportunity.',
            timestamp: '2 hours ago'
        }
    ];
}