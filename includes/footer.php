
  <!-- Toast -->
  <div class="toast-container position-fixed top-0 end-0 p-3" id="toastWrapper">
      <div id="liveToast" class="toast" role="status" aria-live="polite" aria-atomic="true">
          <div class="toast-header">
              <strong class="me-auto"><?= BRAND_NAME ?></strong>
              <small>Just now</small>
              <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
          <div class="toast-body" id="toastMsg"></div>
      </div>
  </div>




  <div class="modal fade" id="displayDetails" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content">
              <div class="modal-header">
                  <h1 class="modal-title fs-5" id="detailModalLabel">Modal title</h1>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                  <div id="detailModalBody"></div>
              </div>
              <div class="modal-footer">
                  <div id="detailModalButtons"></div>
              </div>
          </div>
      </div>
  </div>

 

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <!-- SweetAlert2 CDN -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- html2pdf.js CDN --> 
    <script type="module" src="<?php echo BASE_URL; ?>assets/js/app.js"></script>

  <script>
      AOS.init({
          duration: 800,
          once: true
      });
  </script>