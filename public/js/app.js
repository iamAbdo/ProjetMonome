document.addEventListener('DOMContentLoaded', () => {
  const chips = document.querySelectorAll('[data-filter]');
  const productCards = document.querySelectorAll('[data-category]');
  const modal = document.querySelector('#order-modal');
  const closeBtn = document.querySelector('#modal-close');
  const form = document.querySelector('#order-form');
  const triggerButtons = document.querySelectorAll('[data-order]');
  const statusBox = document.querySelector('#order-status');

  chips.forEach((chip) => {
    chip.addEventListener('click', () => {
      chips.forEach((el) => el.classList.remove('active'));
      chip.classList.add('active');
      const filter = chip.dataset.filter;

      productCards.forEach((card) => {
        if (filter === 'all' || card.dataset.category === filter) {
          card.style.display = 'flex';
        } else {
          card.style.display = 'none';
        }
      });
    });
  });

  if (modal) {
    triggerButtons.forEach((btn) => {
      btn.addEventListener('click', () => {
        modal.classList.add('show');
        const productInput = modal.querySelector('[name="product_id"]');
        const productLabel = modal.querySelector('#product-label');
        if (productInput) {
          productInput.value = btn.dataset.id || '';
        }
        if (productLabel) {
          productLabel.textContent = btn.dataset.name || '';
        }
      });
    });
  }

  if (modal && closeBtn) {
    [closeBtn, modal].forEach((el) => {
      el.addEventListener('click', (event) => {
        if (event.target === el) {
          modal.classList.remove('show');
          if (form) {
            form.reset();
          }
          if (statusBox) {
            statusBox.textContent = '';
            statusBox.className = '';
          }
        }
      });
    });

    const modalContent = modal.querySelector('.modal-content');
    if (modalContent) {
      modalContent.addEventListener('click', (event) => event.stopPropagation());
    }
  }

  if (form && statusBox) {
    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      statusBox.textContent = 'Envoi en cours...';
      statusBox.className = 'alert';

      try {
        const response = await fetch('order-handler.php', {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body: new FormData(form)
        });

        const data = await response.json();

        if (!response.ok) {
          throw new Error(data.message || 'Erreur serveur');
        }

        statusBox.textContent = data.message;
        statusBox.className = 'alert alert-success';
        form.reset();
      } catch (error) {
        statusBox.textContent = error.message;
        statusBox.className = 'alert alert-error';
      }
    });
  }
});


