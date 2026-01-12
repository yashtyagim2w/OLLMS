/**
 * OLLMS - SweetAlert2 Helper Functions
 */

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true
});

const SwalHelper = {
    success: (title, text) => Toast.fire({ icon: 'success', title: title, text: text }),
    error: (title, text) => Toast.fire({ icon: 'error', title: title, text: text }),
    warning: (title, text) => Toast.fire({ icon: 'warning', title: title, text: text }),
    info: (title, text) => Toast.fire({ icon: 'info', title: title, text: text }),

    confirm: (title, text, confirmText = 'Yes', cancelText = 'Cancel') => {
        return Swal.fire({
            title: title,
            text: text,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#1a3a5c',
            cancelButtonColor: '#6c757d',
            confirmButtonText: confirmText,
            cancelButtonText: cancelText
        });
    },

    confirmDanger: (title, text) => {
        return Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        });
    },

    loading: (title = 'Processing...') => {
        Swal.fire({
            title: title,
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
    },

    close: () => Swal.close(),

    inputPrompt: (title, inputPlaceholder = '') => {
        return Swal.fire({
            title: title,
            input: 'textarea',
            inputPlaceholder: inputPlaceholder,
            showCancelButton: true,
            confirmButtonColor: '#1a3a5c'
        });
    }
};

window.SwalHelper = SwalHelper;
