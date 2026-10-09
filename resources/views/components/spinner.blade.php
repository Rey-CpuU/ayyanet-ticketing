<div class="spinner" role="status" aria-label="Loading">
    <div class="spinner-ring"></div>
</div>

<style>
    .spinner {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
    }

    .spinner-ring {
        width: 20px;
        height: 20px;
        border: 3px solid rgba(139, 92, 246, 0.15);
        border-top-color: #8b5cf6;
        border-radius: 50%;
        animation: spin 0.6s linear infinite;
    }

    .spinner.large {
        width: 40px;
        height: 40px;
    }

    .spinner.large .spinner-ring {
        width: 40px;
        height: 40px;
        border-width: 4px;
    }

    .spinner.small {
        width: 14px;
        height: 14px;
    }

    .spinner.small .spinner-ring {
        width: 14px;
        height: 14px;
        border-width: 2px;
    }

    .spinner.white .spinner-ring {
        border-color: rgba(255, 255, 255, 0.15);
        border-top-color: #ffffff;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    .btn-loading {
        position: relative;
        pointer-events: none;
        opacity: 0.7;
    }

    .btn-loading .spinner {
        margin-right: 8px;
    }
</style>