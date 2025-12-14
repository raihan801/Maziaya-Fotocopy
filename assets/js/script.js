// JavaScript untuk Maziaya Fotocopy

document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Auto-hide alerts setelah 5 detik
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    function confirmDelete(serviceId) {
    if (confirm('Apakah Anda yakin ingin menghapus layanan ini?')) {
        window.location.href = 'services.php?action=delete&id=' + serviceId;
    }
}

function confirmDelete(orderId) {
    if (confirm('Apakah Anda yakin ingin menghapus pesanan ini? Tindakan ini tidak dapat dibatalkan.')) {
        window.location.href = 'orders.php?action=delete&id=' + orderId;
    }
}
  
// Handle delete action from URL
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('action') === 'delete' && urlParams.get('id')) {
    confirmDelete(urlParams.get('id'));
}
 
    // Konfirmasi sebelum menghapus
    var deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            if (!confirm('Apakah Anda yakin ingin menghapus?')) {
                e.preventDefault();
            }
        });
    });

    // Format input harga
    var priceInputs = document.querySelectorAll('.price-input');
    priceInputs.forEach(function(input) {
        input.addEventListener('blur', function() {
            var value = parseFloat(this.value.replace(/[^\d]/g, ''));
            if (!isNaN(value)) {
                this.value = 'Rp ' + value.toLocaleString('id-ID');
            }
        });

        input.addEventListener('focus', function() {
            this.value = this.value.replace(/[^\d]/g, '');
        });
    });

    // Preview gambar sebelum upload
    var fileInputs = document.querySelectorAll('.file-input-preview');
    fileInputs.forEach(function(input) {
        input.addEventListener('change', function(e) {
            var file = e.target.files[0];
            var preview = document.getElementById('file-preview');
            
            if (preview && file) {
                if (file.type.startsWith('image/')) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        preview.innerHTML = '<img src="' + e.target.result + '" class="img-fluid" style="max-height: 200px;">';
                    };
                    reader.readAsDataURL(file);
                } else {
                    preview.innerHTML = '<div class="alert alert-info">File: ' + file.name + '</div>';
                }
            }
        });
    });

    // Auto-calculate untuk form pesanan
    var calculateInputs = document.querySelectorAll('[data-calculate]');
    calculateInputs.forEach(function(input) {
        input.addEventListener('input', calculateTotal);
        input.addEventListener('change', calculateTotal);
    });

    function calculateTotal() {
        var total = 0;
        var basePrice = parseFloat(document.getElementById('base_price').value) || 0;
        var quantity = parseFloat(document.getElementById('quantity').value) || 0;
        var extras = document.querySelectorAll('.extra-cost:checked');
        
        extras.forEach(function(extra) {
            total += parseFloat(extra.value) || 0;
        });
        
        total += basePrice * quantity;
        document.getElementById('total_price').value = 'Rp ' + total.toLocaleString('id-ID');
        document.getElementById('display_total').textContent = 'Rp ' + total.toLocaleString('id-ID');
    }
});

// Fungsi untuk menampilkan modal konfirmasi
function showConfirmationModal(title, message, confirmCallback) {
    var modal = document.getElementById('confirmationModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'confirmationModal';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${title}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>${message}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" id="confirmButton">Ya, Lanjutkan</button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }

    var modalInstance = new bootstrap.Modal(modal);
    modalInstance.show();

    document.getElementById('confirmButton').onclick = function() {
        confirmCallback();
        modalInstance.hide();
    };
}

// Fungsi untuk menampilkan loading
function showLoading(button) {
    var originalText = button.innerHTML;
    button.innerHTML = '<span class="loading"></span> Memproses...';
    button.disabled = true;
    
    return function() {
        button.innerHTML = originalText;
        button.disabled = false;
    };
}

// Fungsi untuk format tanggal
function formatDate(dateString) {
    var date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Fungsi untuk copy teks ke clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show success message
        var toast = document.createElement('div');
        toast.className = 'toast align-items-center text-white bg-success border-0 position-fixed bottom-0 end-0 m-3';
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    Berhasil disalin ke clipboard
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        document.body.appendChild(toast);
        
        var toastInstance = new bootstrap.Toast(toast);
        toastInstance.show();
        
        setTimeout(function() {
            document.body.removeChild(toast);
        }, 3000);
    });
}
