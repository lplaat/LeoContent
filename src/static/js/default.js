document.addEventListener('DOMContentLoaded', () => {
    $('.ajax-request').on('submit', (event) => {
        event.preventDefault();

        const form = $(event.target);
        $.ajax({
            url: form.attr('action'),
            dataType: 'json',
            method: 'POST',
            data: form.serialize(),

            success: async function (data) {
                if(data.success) {
                    if(data.message !== undefined) {
                        new Notify({
                            status: 'success',
                            text: data.message,
                        });
                    }
                } else {
                    if(data.message !== undefined) {
                        new Notify({
                            status: 'error',
                            text: data.message,
                        });
                    }
                }

                if(data.redirect !== undefined) {
                    if(data.message !== undefined) {
                        await new Promise(r => setTimeout(r, 1000));
                    }

                    location.href = data.redirect;
                }
            }
        });
    });
});

function createDataTable(tableId, columns, endpoint, action, data={}) {
    // Validate inputs
    if (!tableId || !columns || !endpoint) {
        console.error('Missing required parameters');
        return null;
    }

    // Ensure the table exists in the DOM
    if ($(`#${tableId}`).length === 0) {
        console.error(`Table with ID ${tableId} not found`);
        return null;
    }

    // Prepare column configuration for DataTable
    const columnConfig = columns.map(col => ({
        data: col,
        title: col.charAt(0).toUpperCase() + col.slice(1)
    }));

    // Initialize DataTable
    return $(`#${tableId}`).DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: endpoint,
            type: 'POST',
            contentType: 'application/x-www-form-urlencoded',
            data: function(d) {
                d = {...d, ...data}
                d.action = action;

                return d;
            },
        },
        columns: columnConfig,
        // Optional additional DataTable configurations
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        responsive: true,
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>'
        },
        // Error handling
        error: function(xhr, error, thrown) {
            console.error('DataTables error:', error, thrown);
        }
    });
}
