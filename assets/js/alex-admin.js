/**
 * ALEX TRAVEL CONSULTANT - Admin JavaScript
 */

(function() {
    'use strict';

    // Handle lead status updates
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form[action*="admin-ajax"]');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (this.action.includes('alex_update_lead_status')) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    const leadId = formData.get('lead_id');
                    const status = formData.get('status');

                    fetch(ajaxurl, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Lead status updated successfully!');
                            location.reload();
                        } else {
                            alert('Error updating lead status.');
                        }
                    })
                    .catch(error => console.error('Error:', error));
                }
            });
        }
    });

})();
