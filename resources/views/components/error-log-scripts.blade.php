<script>
    document.addEventListener('DOMContentLoaded', function() {
        window.addEventListener('clear-error-log-entry', function(event) {
            const { streamId, index } = event.detail;
            
            if (confirm('Are you sure you want to clear this error log entry?')) {
                // Get the CSRF token from the meta tag
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                // Send AJAX request to clear the error log entry
                fetch('/admin/clear-error-log-entry', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        stream_id: streamId,
                        index: index
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload the page to show the updated error logs
                        window.location.reload();
                    } else {
                        alert('Failed to clear error log entry: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while clearing the error log entry');
                });
            }
        });
    });
</script> 