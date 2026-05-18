// Campus Event Hub - Main JavaScript

const API_BASE = 'api/';
let currentUser = null;
let currentPage = 1;
const pageSize = 12;

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    checkUserSession();
    if (document.getElementById('events-grid')) {
        loadEvents();
        loadCategories();
    }
});

/**
 * Check if user is logged in
 */
function checkUserSession() {
    fetch(API_BASE + 'auth.php?action=check_session')
        .then(response => response.json())
        .then(data => {
            if (data.logged_in) {
                currentUser = data.user;
                updateNavbar(true);
            } else {
                updateNavbar(false);
            }
        })
        .catch(error => console.error('Error checking session:', error));
}

/**
 * Update navbar based on login status
 */
function updateNavbar(isLoggedIn) {
    const loginBtn = document.getElementById('login-btn');
    const logoutBtn = document.getElementById('logout-btn');

    if (isLoggedIn) {
        if (loginBtn) loginBtn.style.display = 'none';
        if (logoutBtn) logoutBtn.style.display = 'block';
    } else {
        if (loginBtn) loginBtn.style.display = 'block';
        if (logoutBtn) logoutBtn.style.display = 'none';
    }
}

/**
 * Load upcoming events
 */
function loadEvents(categoryId = null, searchQuery = null) {
    let url = API_BASE + 'get_events.php?limit=' + pageSize + '&offset=' + ((currentPage - 1) * pageSize);

    if (categoryId) {
        url += '&category_id=' + categoryId;
    }
    if (searchQuery) {
        url += '&search=' + encodeURIComponent(searchQuery);
    }

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayEvents(data.data);
                updatePagination();
            }
        })
        .catch(error => console.error('Error loading events:', error));
}

/**
 * Display events in grid
 */
function displayEvents(events) {
    const eventsGrid = document.getElementById('events-grid');
    if (!eventsGrid) return;

    eventsGrid.innerHTML = '';

    if (events.length === 0) {
        eventsGrid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 2rem; color: #999;">No events found.</div>';
        return;
    }

    events.forEach(event => {
        const card = createEventCard(event);
        eventsGrid.appendChild(card);
    });
}

/**
 * Create event card
 */
function createEventCard(event) {
    const card = document.createElement('div');
    card.className = 'event-card';
    card.onclick = () => viewEventDetails(event.id);

    const eventDate = new Date(event.event_date);
    const dateStr = eventDate.toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });

    card.innerHTML = `
        <div class="event-image">
            ${event.image_url ? `<img src="${event.image_url}" alt="${event.title}">` : '📅'}
        </div>
        <div class="event-card-body">
            <div class="event-category" style="background-color: ${event.category_color || '#e8f4f8'}20; color: ${event.category_color || '#2980b9'}">
                ${event.category_name || 'Event'}
            </div>
            <h3 class="event-title">${event.title}</h3>
            <div class="event-info">📍 ${event.location}</div>
            <div class="event-info">🕒 ${dateStr}</div>
            <div class="event-description">${event.description.substring(0, 100)}...</div>
            <div class="event-footer">
                <span class="event-attendees">👥 ${event.rsvp_count || 0} confirmed</span>
                <button class="btn-view">View Details</button>
            </div>
        </div>
    `;

    return card;
}

/**
 * Load event categories
 */
function loadCategories() {
    fetch(API_BASE + 'get_events.php')
        .then(response => response.json())
        .then(data => {
            // We need to add a dedicated endpoint for categories
            // For now, using static categories
            displayCategories();
        })
        .catch(error => console.error('Error loading categories:', error));
}

/**
 * Display categories
 */
function displayCategories() {
    const categories = [
        { id: 1, name: 'Seminar' },
        { id: 2, name: 'Sports' },
        { id: 3, name: 'Cultural' },
        { id: 4, name: 'Social' },
        { id: 5, name: 'Career' },
        { id: 6, name: 'Technology' }
    ];

    const container = document.getElementById('category-filters');
    if (!container) return;

    categories.forEach(cat => {
        const btn = document.createElement('button');
        btn.className = 'filter-btn';
        btn.textContent = cat.name;
        btn.onclick = () => {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            filterByCategory(cat.id);
        };
        container.appendChild(btn);
    });
}

/**
 * Filter events by category
 */
function filterByCategory(categoryId) {
    currentPage = 1;
    loadEvents(categoryId);
}

/**
 * Search events
 */
function searchEvents() {
    const query = document.getElementById('search-input')?.value;
    if (query) {
        currentPage = 1;
        loadEvents(null, query);
    }
}

/**
 * View event details
 */
function viewEventDetails(eventId) {
    fetch(API_BASE + 'get_event_details.php?id=' + eventId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayEventDetails(data.data);
                document.getElementById('event-modal').classList.add('show');
            }
        })
        .catch(error => console.error('Error loading event details:', error));
}

/**
 * Display event details
 */
function displayEventDetails(event) {
    const content = document.getElementById('event-details-content');
    if (!content) return;

    const eventDate = new Date(event.event_date);
    const dateStr = eventDate.toLocaleDateString('en-US', { 
        weekday: 'long',
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });

    const rsvpStatus = currentUser && event.user_rsvp_status ? event.user_rsvp_status : null;

    content.innerHTML = `
        <div class="event-details">
            <div class="event-header">
                <h2>${event.title}</h2>
                <div class="event-category-badge" style="background-color: ${event.category_color || '#3498db'}30">
                    ${event.category_name}
                </div>
            </div>

            <div class="event-body">
                <div class="event-meta">
                    <div class="meta-item">
                        <span class="meta-icon">📅</span>
                        <div class="meta-content">
                            <h4>Date & Time</h4>
                            <p>${dateStr}</p>
                        </div>
                    </div>

                    <div class="meta-item">
                        <span class="meta-icon">📍</span>
                        <div class="meta-content">
                            <h4>Location</h4>
                            <p>${event.location}</p>
                        </div>
                    </div>

                    <div class="meta-item">
                        <span class="meta-icon">👥</span>
                        <div class="meta-content">
                            <h4>Attendees</h4>
                            <p>${event.rsvp_count} confirmed / ${event.capacity}</p>
                        </div>
                    </div>
                </div>

                <div class="event-description">
                    <h3>About This Event</h3>
                    <p>${event.description}</p>
                </div>

                ${event.organizer_name ? `
                    <div class="organizer-info">
                        <h4>Organized by</h4>
                        <p>${event.organizer_name}</p>
                        <a href="mailto:${event.organizer_email}" class="organizer-email">${event.organizer_email}</a>
                    </div>
                ` : ''}

                ${currentUser ? `
                    <div class="rsvp-section">
                        <h4>Your Response</h4>
                        <div class="rsvp-status">
                            <button class="rsvp-btn ${rsvpStatus === 'confirmed' ? 'selected' : ''}" onclick="rsvpEvent(${event.id}, 'confirmed')">
                                ✓ Confirmed
                            </button>
                            <button class="rsvp-btn ${rsvpStatus === 'interested' ? 'selected' : ''}" onclick="rsvpEvent(${event.id}, 'interested')">
                                ♡ Interested
                            </button>
                            <button class="rsvp-btn ${rsvpStatus === 'declined' ? 'selected' : ''}" onclick="rsvpEvent(${event.id}, 'declined')">
                                ✗ Declined
                            </button>
                        </div>
                    </div>

                    <div class="reminder-section">
                        <h4>📬 Set Reminder</h4>
                        <div class="reminder-options">
                            <div class="reminder-option">
                                <input type="radio" id="email-reminder" name="reminder" value="email" checked>
                                <label for="email-reminder">Email</label>
                            </div>
                            <div class="reminder-option">
                                <input type="radio" id="sms-reminder" name="reminder" value="sms">
                                <label for="sms-reminder">SMS</label>
                            </div>
                            <div class="reminder-option">
                                <input type="radio" id="inapp-reminder" name="reminder" value="in_app">
                                <label for="inapp-reminder">In-app</label>
                            </div>
                        </div>
                        <div class="reminder-time">
                            <input type="number" id="reminder-time" value="24" min="1" max="72">
                            <select id="reminder-unit">
                                <option value="hours">Hours before</option>
                                <option value="minutes">Minutes before</option>
                            </select>
                            <button class="btn-success" onclick="setReminder(${event.id})">Set</button>
                        </div>
                    </div>
                ` : `
                    <div style="text-align: center; padding: 2rem; background-color: #f9f9f9; border-radius: 8px;">
                        <p>Please <a href="login.php" style="color: #3498db;">login</a> to RSVP and set reminders</p>
                    </div>
                `}
            </div>
        </div>
    `;
}

/**
 * Load my events
 */
function loadMyEvents() {
    if (!currentUser) {
        alert('Please login first');
        window.location.href = 'login.php';
        return;
    }

    fetch(API_BASE + 'rsvp.php?action=my_rsvps&status=confirmed')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayMyEvents(data.data);
                document.getElementById('my-events-modal').classList.add('show');
            }
        })
        .catch(error => console.error('Error loading my events:', error));
}

/**
 * Display my events
 */
function displayMyEvents(events) {
    const content = document.getElementById('my-events-list');
    if (!content) return;

    content.innerHTML = '';

    if (events.length === 0) {
        content.innerHTML = '<p style="text-align: center; color: #999;">You haven\'t registered for any events yet.</p>';
        return;
    }

    events.forEach(event => {
        const eventDate = new Date(event.event_date);
        const dateStr = eventDate.toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        const item = document.createElement('div');
        item.className = 'my-event-item';
        item.innerHTML = `
            <div class="my-event-info">
                <h4>${event.title}</h4>
                <p>📍 ${event.location}</p>
                <p>📅 ${dateStr}</p>
            </div>
            <div class="my-event-status confirmed">${event.status}</div>
        `;
        content.appendChild(item);
    });
}

/**
 * Open profile
 */
function openProfile() {
    if (!currentUser) {
        alert('Please login first');
        window.location.href = 'login.php';
        return;
    }

    fetch(API_BASE + 'user_profile.php?action=profile')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayProfile(data.user);
                document.getElementById('profile-modal').classList.add('show');
            }
        })
        .catch(error => console.error('Error loading profile:', error));
}

/**
 * Display profile
 */
function displayProfile(user) {
    const content = document.getElementById('profile-content');
    if (!content) return;

    content.innerHTML = `
        <div style="display: grid; gap: 1rem;">
            <div>
                <label style="font-weight: bold; color: #999;">Student ID</label>
                <p>${user.student_id}</p>
            </div>
            <div>
                <label style="font-weight: bold; color: #999;">Name</label>
                <p>${user.name}</p>
            </div>
            <div>
                <label style="font-weight: bold; color: #999;">Email</label>
                <p>${user.email}</p>
            </div>
            <div>
                <label style="font-weight: bold; color: #999;">Department</label>
                <p>${user.department || 'Not specified'}</p>
            </div>
            <div>
                <label style="font-weight: bold; color: #999;">Phone</label>
                <p>${user.phone || 'Not specified'}</p>
            </div>
        </div>
    `;
}

/**
 * RSVP to event
 */
function rsvpEvent(eventId, status) {
    if (!currentUser) {
        alert('Please login first');
        return;
    }

    fetch(API_BASE + 'rsvp.php?action=rsvp', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            event_id: eventId,
            status: status
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('RSVP updated successfully!');
            // Update button states
            document.querySelectorAll('.rsvp-btn').forEach(btn => btn.classList.remove('selected'));
            event.target.classList.add('selected');
            viewEventDetails(eventId);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => console.error('Error RSVP:', error));
}

/**
 * Set reminder
 */
function setReminder(eventId) {
    if (!currentUser) {
        alert('Please login first');
        return;
    }

    const reminderType = document.querySelector('input[name="reminder"]:checked')?.value || 'email';
    const reminderTime = parseInt(document.getElementById('reminder-time')?.value || 24);
    const reminderUnit = document.getElementById('reminder-unit')?.value || 'hours';

    fetch(API_BASE + 'reminder.php?action=set', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            event_id: eventId,
            reminder_type: reminderType,
            reminder_time: reminderTime,
            reminder_unit: reminderUnit
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Reminder set successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => console.error('Error setting reminder:', error));
}

/**
 * Logout
 */
function logout() {
    fetch(API_BASE + 'auth.php?action=logout')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentUser = null;
                updateNavbar(false);
                alert('Logged out successfully!');
                window.location.href = 'index.php';
            }
        })
        .catch(error => console.error('Error logging out:', error));
}

/**
 * Close modals
 */
function closeEventModal() {
    document.getElementById('event-modal').classList.remove('show');
}

function closeMyEventsModal() {
    document.getElementById('my-events-modal').classList.remove('show');
}

function closeProfileModal() {
    document.getElementById('profile-modal').classList.remove('show');
}

/**
 * Update pagination
 */
function updatePagination() {
    const paginationDiv = document.getElementById('pagination');
    if (!paginationDiv) return;

    paginationDiv.innerHTML = `
        <button onclick="goToPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>Previous</button>
        <span style="padding: 0.7rem 1rem;">Page ${currentPage}</span>
        <button onclick="goToPage(${currentPage + 1})">Next</button>
    `;
}

/**
 * Go to page
 */
function goToPage(pageNum) {
    if (pageNum < 1) return;
    currentPage = pageNum;
    loadEvents();
    window.scrollTo(0, 0);
}

// Close modal when clicking outside
window.onclick = function(event) {
    const eventModal = document.getElementById('event-modal');
    const myEventsModal = document.getElementById('my-events-modal');
    const profileModal = document.getElementById('profile-modal');

    if (event.target === eventModal) {
        eventModal.classList.remove('show');
    }
    if (event.target === myEventsModal) {
        myEventsModal.classList.remove('show');
    }
    if (event.target === profileModal) {
        profileModal.classList.remove('show');
    }
}
