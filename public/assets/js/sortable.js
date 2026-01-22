/**
 * Sortable Module
 * Reusable drag-drop functionality for tables/lists
 * Uses HTML5 Drag & Drop API
 */
(function () {
    'use strict';

    /**
     * Initialize sortable functionality on a container
     * 
     * @param {Object} options
     * @param {string} options.containerSelector - CSS selector for the container (tbody/ul)
     * @param {string} options.itemSelector - CSS selector for draggable items (tr/li)
     * @param {string} options.handleSelector - CSS selector for drag handle (optional)
     * @param {Function} options.onReorder - Callback with new order array of IDs
     * @param {string} options.idAttribute - Data attribute for item ID (default: 'data-id')
     */
    function initSortable(options) {
        const container = document.querySelector(options.containerSelector);
        if (!container) {
            console.warn('Sortable: Container not found:', options.containerSelector);
            return;
        }

        const itemSelector = options.itemSelector || 'tr';
        const handleSelector = options.handleSelector || '.drag-handle';
        const idAttribute = options.idAttribute || 'data-id';
        const onReorder = options.onReorder || function () { };

        let draggedItem = null;

        // Add event listeners to container (delegation)
        container.addEventListener('dragstart', handleDragStart);
        container.addEventListener('dragend', handleDragEnd);
        container.addEventListener('dragover', handleDragOver);
        container.addEventListener('drop', handleDrop);
        container.addEventListener('dragenter', handleDragEnter);
        container.addEventListener('dragleave', handleDragLeave);

        // Make items draggable via handle
        function setupDraggable(item) {
            const handle = item.querySelector(handleSelector);
            if (handle) {
                handle.style.cursor = 'grab';
                handle.addEventListener('mousedown', () => {
                    item.setAttribute('draggable', 'true');
                });
                handle.addEventListener('mouseup', () => {
                    item.setAttribute('draggable', 'false');
                });
            } else {
                // If no handle, make entire item draggable
                item.setAttribute('draggable', 'true');
                item.style.cursor = 'grab';
            }
        }

        // Mutation observer to handle dynamically added items
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1 && node.matches(itemSelector)) {
                        setupDraggable(node);
                    }
                });
            });
        });

        observer.observe(container, { childList: true });

        // Setup existing items
        container.querySelectorAll(itemSelector).forEach(setupDraggable);

        function handleDragStart(e) {
            const item = e.target.closest(itemSelector);
            if (!item) return;

            draggedItem = item;
            item.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', item.getAttribute(idAttribute));

            // Add slight delay for visual feedback
            setTimeout(() => {
                item.style.opacity = '0.5';
            }, 0);
        }

        function handleDragEnd(e) {
            const item = e.target.closest(itemSelector);
            if (!item) return;

            item.classList.remove('dragging');
            item.style.opacity = '';
            item.setAttribute('draggable', 'false');
            draggedItem = null;

            // Remove all drag-over styles
            container.querySelectorAll(itemSelector).forEach(el => {
                el.classList.remove('drag-over');
            });
        }

        function handleDragOver(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';

            const item = e.target.closest(itemSelector);
            if (!item || item === draggedItem) return;

            const rect = item.getBoundingClientRect();
            const midpoint = rect.top + rect.height / 2;

            if (e.clientY < midpoint) {
                container.insertBefore(draggedItem, item);
            } else {
                container.insertBefore(draggedItem, item.nextSibling);
            }
        }

        function handleDrop(e) {
            e.preventDefault();

            // Only proceed if there was an actual drag
            if (!draggedItem) return;

            // Get new order
            const items = container.querySelectorAll(itemSelector);
            const newOrder = Array.from(items)
                .map(item => item.getAttribute(idAttribute))
                .filter(id => id !== null && id !== '');

            // Only call reorder if we have valid items
            if (newOrder.length === 0) return;

            // Update order numbers visually
            items.forEach((item, index) => {
                const orderCell = item.querySelector('.order-number');
                if (orderCell) {
                    orderCell.textContent = index + 1;
                }
            });

            // Callback with new order
            onReorder(newOrder);
        }

        function handleDragEnter(e) {
            const item = e.target.closest(itemSelector);
            if (item && item !== draggedItem) {
                item.classList.add('drag-over');
            }
        }

        function handleDragLeave(e) {
            const item = e.target.closest(itemSelector);
            if (item) {
                item.classList.remove('drag-over');
            }
        }

        // Return public methods
        return {
            refresh: function () {
                container.querySelectorAll(itemSelector).forEach(setupDraggable);
            },
            destroy: function () {
                observer.disconnect();
                container.removeEventListener('dragstart', handleDragStart);
                container.removeEventListener('dragend', handleDragEnd);
                container.removeEventListener('dragover', handleDragOver);
                container.removeEventListener('drop', handleDrop);
                container.removeEventListener('dragenter', handleDragEnter);
                container.removeEventListener('dragleave', handleDragLeave);
            }
        };
    }

    // Expose globally
    window.initSortable = initSortable;
})();
