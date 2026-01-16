/**
 * OLLMS - Time Utilities
 * Global time formatting and relative time functions
 * Exposes functions via window.TimeUtils
 */

(function () {
    /**
     * Get relative time string (e.g., "2h ago", "3d ago")
     * @param {Date|string} date - Date object or date string
     * @returns {string} Relative time string
     */
    function getTimeAgo(date) {
        const dateObj = date instanceof Date ? date : new Date(date);
        const seconds = Math.floor((new Date() - dateObj) / 1000);

        let interval = seconds / 31536000;
        if (interval > 1) return Math.floor(interval) + "y ago";

        interval = seconds / 2592000;
        if (interval > 1) return Math.floor(interval) + "mo ago";

        interval = seconds / 86400;
        if (interval > 1) return Math.floor(interval) + "d ago";

        interval = seconds / 3600;
        if (interval > 1) return Math.floor(interval) + "h ago";

        interval = seconds / 60;
        if (interval > 1) return Math.floor(interval) + "m ago";

        return Math.floor(seconds) + "s ago";
    }

    /**
     * Format date as dd/mm/yyyy HH:MM:SS AM/PM in user's local timezone
     * Assumes input is in UTC
     * @param {string} dateString - Date string (assumed UTC)
     * @returns {string} Formatted date string
     */
    function formatDateTime(dateString) {
        if (!dateString) return '';

        // Parse as UTC by appending ' UTC' if not already a valid ISO string
        let date;
        if (dateString.includes('T') || dateString.includes('Z')) {
            date = new Date(dateString);
        } else {
            date = new Date(dateString + ' UTC');
        }

        if (isNaN(date.getTime())) return dateString;

        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();

        let hours = date.getHours();
        const minutes = String(date.getMinutes()).padStart(2, '0');
        const seconds = String(date.getSeconds()).padStart(2, '0');

        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12; // 0 should be 12
        const formattedHours = String(hours).padStart(2, '0');

        return `${day}/${month}/${year} ${formattedHours}:${minutes}:${seconds} ${ampm}`;
    }

    /**
     * Format date as dd/mm/yyyy (without time)
     * @param {string} dateString - Date string
     * @returns {string} Formatted date string
     */
    function formatDate(dateString) {
        if (!dateString) return '';

        const date = new Date(dateString);
        if (isNaN(date.getTime())) return dateString;

        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();

        return `${day}/${month}/${year}`;
    }

    /**
     * Format time as HH:MM:SS AM/PM
     * @param {string} dateString - Date string
     * @returns {string} Formatted time string
     */
    function formatTime(dateString) {
        if (!dateString) return '';

        const date = new Date(dateString);
        if (isNaN(date.getTime())) return dateString;

        let hours = date.getHours();
        const minutes = String(date.getMinutes()).padStart(2, '0');
        const seconds = String(date.getSeconds()).padStart(2, '0');

        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12;
        const formattedHours = String(hours).padStart(2, '0');

        return `${formattedHours}:${minutes}:${seconds} ${ampm}`;
    }

    /**
     * Get simple file name from S3 URL (removes signature params)
     * @param {string} url - S3 presigned URL
     * @returns {string} Simple file name
     */
    function getSimpleFileName(url) {
        if (!url) return 'Document';
        try {
            const urlObj = new URL(url);
            const pathname = urlObj.pathname;
            const fileName = pathname.split('/').pop();

            // Return just filename without timestamp prefix if present
            if (fileName.includes('_')) {
                const parts = fileName.split('_');
                if (parts.length > 1 && !isNaN(parts[0])) {
                    return parts.slice(1).join('_');
                }
            }
            return fileName || 'Document';
        } catch (e) {
            return 'Document';
        }
    }

    // Expose functions globally
    window.TimeUtils = {
        getTimeAgo: getTimeAgo,
        formatDateTime: formatDateTime,
        formatDate: formatDate,
        formatTime: formatTime,
        getSimpleFileName: getSimpleFileName
    };
})();
