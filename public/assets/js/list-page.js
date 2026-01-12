/**
 * List Page Utility
 * Initializes list pages with filtering, searching, and pagination
 * 
 * Usage:
 * import initializeListPage from './list-page.js';
 * 
 * initializeListPage({
 *     apiEndpoint: '/api/users',
 *     renderRow: ({ row, rowNumber }) => `<tr>...</tr>`,
 *     columnCount: 5
 * });
 */
import { renderPagination } from "./pagination.js";

export default function initializeListPage(config) {
    const { apiEndpoint, renderRow, columnCount, onDataLoaded } = config;

    const filtersForm = document.getElementById("filtersForm");
    const tableBody = document.getElementById("main-table");
    const paginationContainer = document.getElementById("paginationContainer");

    let currentPage = 1;

    function debounce(fn, delay) {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    // Read URL params and populate form fields
    function applyUrlParamsToForm() {
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.forEach((value, key) => {
            const field = filtersForm?.querySelector(`[name="${key}"]`);
            if (field) {
                field.value = value;
            }
        });
    }

    async function fetchData() {
        try {
            showMessage("Loading...", "var(--gray-500)", true);

            const params = new URLSearchParams();

            if (filtersForm) {
                const formData = new FormData(filtersForm);
                formData.forEach((value, key) => {
                    if (value) params.set(key, value);
                });
            }

            params.set("page", currentPage);

            const res = await fetch(`${apiEndpoint}?${params}`);

            if (!res.ok) {
                throw new Error("Network error");
            }

            const { success, data, message } = await res.json();

            if (!success) {
                throw new Error(message || "Failed to fetch data");
            }

            renderTable(data.data, data.pagination);

            // Callback after data loaded
            if (onDataLoaded) {
                onDataLoaded(data.data, data.pagination);
            }

        } catch (err) {
            console.error(err);
            showMessage("Error loading data. Please try again.", "var(--danger-color)");
        }
    }

    function showMessage(msg, color = "var(--gray-600)", isLoading = false) {
        if (!tableBody) return;

        const isTable = tableBody.tagName === 'TBODY' || tableBody.tagName === 'TABLE';

        const content = isLoading
            ? `<div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>${msg}`
            : msg;

        if (isTable) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="${columnCount}" style="text-align:center; color:${color}; padding: 40px;">
                        ${content}
                    </td>
                </tr>
            `;
        } else {
            tableBody.innerHTML = `
                <li style="text-align:center; color:${color}; padding: 20px; list-style:none;">
                    ${content}
                </li>
            `;
        }
    }

    function reload() {
        currentPage = 1;
        fetchData();
    }

    function reloadCurrentPage() {
        fetchData();
    }

    function renderTable(data, pagination) {
        if (!tableBody) return;

        tableBody.innerHTML = "";

        if (paginationContainer) {
            renderPagination(pagination, paginationContainer, (newPage) => {
                currentPage = newPage;
                reloadCurrentPage();
            });
        }

        if (!data || !data.length) {
            showMessage("No records found.");
            return;
        }

        data.forEach((row, index) => {
            const rowNumber = index + 1 + ((pagination.page - 1) * pagination.limit);

            tableBody.innerHTML += renderRow({
                row,
                rowNumber,
                pagination
            });
        });
    }

    // Debounce search input
    const searchInput = document.getElementById("search_input");
    if (searchInput) {
        searchInput.addEventListener("input", debounce(fetchData, 300));
    }

    // Filter dropdowns
    if (filtersForm) {
        [...filtersForm.querySelectorAll("select")].forEach(sel => {
            sel.addEventListener("change", () => {
                reload();
            });
        });
    }

    // Reset button
    const resetBtn = document.getElementById("resetBtn");
    if (resetBtn) {
        resetBtn.addEventListener("click", () => {
            if (filtersForm) filtersForm.reset();
            // Clear URL params
            window.history.replaceState({}, '', window.location.pathname);
            reload();
        });
    }

    // Apply URL params to form before initial fetch
    applyUrlParamsToForm();

    // Initial fetch 
    fetchData();

    return {
        reload,
        reloadCurrentPage,
        fetchData
    };
}
