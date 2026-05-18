<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Event Hub - Discover Events</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/events.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <h1>Campus Event Hub</h1>
            </div>
            <ul class="navbar-menu" id="navbar-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="#" onclick="loadMyEvents(); return false;">My Events</a></li>
                <li><a href="#" onclick="openProfile(); return false;">Profile</a></li>
                <li><a href="#" onclick="logout(); return false;" id="logout-btn">Logout</a></li>
                <li><a href="login.php" id="login-btn">Login</a></li>
            </ul>
        </div>
    </nav>

    <div class="hero">
        <div class="hero-content">
            <h2>Discover Amazing Campus Events</h2>
            <p>Connect with your community and never miss an event</p>
            <div class="search-container">
                <input type="text" id="search-input" placeholder="Search events...">
                <button onclick="searchEvents()">Search</button>
            </div>
        </div>
    </div>

    <div class="container main-content">
        <div class="filters-section">
            <h3>Filter by Category</h3>
            <div class="category-filters" id="category-filters">
                <button class="filter-btn active" onclick="filterByCategory(null)">All Events</button>
            </div>
        </div>

        <div class="events-grid" id="events-grid">
            <!-- Events will be loaded here -->
        </div>

        <div class="pagination" id="pagination">
            <!-- Pagination will be loaded here -->
        </div>
    </div>

    <!-- Event Details Modal -->
    <div id="event-modal" class="modal">
        <div class="modal-content large">
            <span class="close" onclick="closeEventModal()">&times;</span>
            <div id="event-details-content">
                <!-- Event details will be loaded here -->
            </div>
        </div>
    </div>

    <!-- My Events Modal -->
    <div id="my-events-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeMyEventsModal()">&times;</span>
            <h2>My Registered Events</h2>
            <div id="my-events-list">
                <!-- User's events will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Profile Modal -->
    <div id="profile-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeProfileModal()">&times;</span>
            <h2>My Profile</h2>
            <div id="profile-content">
                <!-- Profile will be loaded here -->
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2026 Campus Event Hub. All rights reserved.</p>
    </footer>

    <script src="js/main.js"></script>
    <script src="js/events.js"></script>
</body>
</html>
