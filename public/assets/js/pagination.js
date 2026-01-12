/**
 * Pagination Component
 * Renders pagination buttons and handles page changes
 */
export function renderPagination(pagination, container, onPageChange) {
    const { page, totalPages } = pagination;
    container.innerHTML = "";
    container.className = "pagination-container";

    if (totalPages <= 1) return;

    const createBtn = (label, pageNum, disabled = false, isActive = false) => {
        const btn = document.createElement("button");
        btn.textContent = label;
        btn.disabled = disabled;
        btn.className = "pagination-btn";
        if (isActive) btn.classList.add("active");

        btn.addEventListener("click", () => {
            if (!disabled && pageNum !== page) {
                onPageChange(pageNum);
            }
        });

        return btn;
    };

    // Previous button
    container.appendChild(
        createBtn("Prev", page - 1, page === 1)
    );

    // Page numbers
    for (let p = 1; p <= totalPages; p++) {
        // Show limited pages for large pagination
        if (totalPages > 7) {
            if (p === 1 || p === totalPages || (p >= page - 1 && p <= page + 1)) {
                container.appendChild(createBtn(String(p), p, false, p === page));
            } else if (p === page - 2 || p === page + 2) {
                const dots = document.createElement("span");
                dots.textContent = "...";
                dots.className = "pagination-dots";
                container.appendChild(dots);
            }
        } else {
            container.appendChild(createBtn(String(p), p, false, p === page));
        }
    }

    // Next button
    container.appendChild(
        createBtn("Next", page + 1, page === totalPages)
    );
}
