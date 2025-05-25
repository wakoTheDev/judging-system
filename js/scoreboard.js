

class ScoreboardManager {
    constructor() {
        this.refreshInterval = 10000; 
        this.intervalId = null;
        this.isUpdating = false;
        this.lastUpdateTime = Date.now();
        
        this.init();
    }
    
    init() {
        // Create refresh indicator
        this.createRefreshIndicator();
        
        // Start auto-refresh
        this.startAutoRefresh();
        
        // Add manual refresh button
        this.addRefreshButton();
        
        console.log('Scoreboard Manager initialized');
    }
    
    createRefreshIndicator() {
        const indicator = document.createElement('div');
        indicator.id = 'refresh-indicator';
        indicator.className = 'refresh-indicator';
        indicator.innerHTML = '🔄 Updating...';
        document.body.appendChild(indicator);
    }
    
    addRefreshButton() {
        const header = document.querySelector('.scoreboard-header');
        if (header) {
            const refreshBtn = document.createElement('button');
            refreshBtn.className = 'btn btn-secondary';
            refreshBtn.innerHTML = '🔄 Refresh Now';
            refreshBtn.style.marginLeft = '1rem';
            refreshBtn.onclick = () => this.updateScoreboard(true);
            
            const title = header.querySelector('h1');
            if (title) {
                title.appendChild(refreshBtn);
            }
        }
    }
    
    startAutoRefresh() {
        this.intervalId = setInterval(() => {
            this.updateScoreboard();
        }, this.refreshInterval);
        
        console.log(`Auto-refresh started: every ${this.refreshInterval/1000} seconds`);
    }
    
    stopAutoRefresh() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
            console.log('Auto-refresh stopped');
        }
    }
    
    async updateScoreboard(manual = false) {
        if (this.isUpdating) return;
        
        this.isUpdating = true;
        this.showRefreshIndicator();
        
        try {
            console.log(manual ? 'Manual refresh triggered' : 'Auto-refresh triggered');
            
            const response = await fetch('api/scoreboard.php?format=simple', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            this.renderScoreboard(data);
            this.lastUpdateTime = Date.now();
            
            console.log(`Scoreboard updated: ${data.length} participants`);
            
        } catch (error) {
            console.error('Failed to update scoreboard:', error);
            this.showError('Failed to update scoreboard. Please refresh the page.');
        } finally {
            this.isUpdating = false;
            this.hideRefreshIndicator();
        }
    }
    
    renderScoreboard(data) {
        const tbody = document.querySelector('#scoreboard-table tbody');
        if (!tbody) {
            console.error('Scoreboard table body not found');
            return;
        }
        
        // Clear existing rows
        tbody.innerHTML = '';
        
        // Sort data by total points (descending)
        data.sort((a, b) => parseInt(b.total_points) - parseInt(a.total_points));
        
        // Render rows
        data.forEach((user, index) => {
            const row = this.createScoreboardRow(user, index + 1);
            tbody.appendChild(row);
        });
        
        // Update last updated time
        this.updateLastUpdatedTime();
    }
    
    createScoreboardRow(user, rank) {
        const row = document.createElement('tr');
        
        // Add ranking class for top 3
        if (rank === 1) row.classList.add('rank-1');
        else if (rank === 2) row.classList.add('rank-2');
        else if (rank === 3) row.classList.add('rank-3');
        
        row.innerHTML = `
            <td><strong>#${rank}</strong></td>
            <td>${this.escapeHtml(user.display_name)}</td>
            <td>@${this.escapeHtml(user.username)}</td>
            <td><span class="points-badge">${user.total_points}</span></td>
        `;
        
        return row;
    }
    
    showRefreshIndicator() {
        const indicator = document.getElementById('refresh-indicator');
        if (indicator) {
            indicator.classList.add('active');
        }
    }
    
    hideRefreshIndicator() {
        const indicator = document.getElementById('refresh-indicator');
        if (indicator) {
            indicator.classList.remove('active');
        }
    }
    
    updateLastUpdatedTime() {
        let timeElement = document.getElementById('last-updated');
        if (!timeElement) {
            timeElement = document.createElement('div');
            timeElement.id = 'last-updated';
            timeElement.style.textAlign = 'center';
            timeElement.style.color = '#666';
            timeElement.style.fontSize = '0.9rem';
            timeElement.style.marginTop = '1rem';
            
            const container = document.querySelector('.scoreboard-table');
            if (container) {
                container.after(timeElement);
            }
        }
        
        const now = new Date();
        timeElement.innerHTML = `Last updated: ${now.toLocaleTimeString()}`;
    }
    
    showError(message) {
        // Remove existing error messages
        const existingError = document.querySelector('.scoreboard-error');
        if (existingError) {
            existingError.remove();
        }
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'message error scoreboard-error';
        errorDiv.textContent = message;
        
        const container = document.querySelector('.main-content');
        if (container) {
            container.insertBefore(errorDiv, container.firstChild);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (errorDiv.parentNode) {
                    errorDiv.remove();
                }
            }, 5000);
        }
    }
    
    escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
    
    // Public methods for external control
    pause() {
        this.stopAutoRefresh();
        console.log('Scoreboard updates paused');
    }
    
    resume() {
        this.startAutoRefresh();
        console.log('Scoreboard updates resumed');
    }
    
    setRefreshInterval(seconds) {
        this.refreshInterval = seconds * 1000;
        if (this.intervalId) {
            this.stopAutoRefresh();
            this.startAutoRefresh();
        }
        console.log(`Refresh interval set to ${seconds} seconds`);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize on scoreboard page
    if (document.getElementById('scoreboard-table')) {
        window.scoreboardManager = new ScoreboardManager();
        
        // Expose control methods to global scope for debugging
        window.pauseScoreboard = () => window.scoreboardManager.pause();
        window.resumeScoreboard = () => window.scoreboardManager.resume();
        window.refreshScoreboard = () => window.scoreboardManager.updateScoreboard(true);
        window.setRefreshInterval = (seconds) => window.scoreboardManager.setRefreshInterval(seconds);
    }
});

// Handle page visibility changes (pause updates when tab is hidden)
document.addEventListener('visibilitychange', function() {
    if (window.scoreboardManager) {
        if (document.hidden) {
            window.scoreboardManager.pause();
        } else {
            window.scoreboardManager.resume();
            // Immediately refresh when tab becomes visible
            window.scoreboardManager.updateScoreboard(true);
        }
    }
});