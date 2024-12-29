document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('contactForm');
  const loadingDiv = form.querySelector('.loading');
  const errorDiv = form.querySelector('.error-message');
  const sentDiv = form.querySelector('.sent-message');

  form.addEventListener('submit', async function (e) {
    e.preventDefault();

    // Réinitialiser les messages
    loadingDiv.classList.remove('d-none');
    errorDiv.style.display = 'none';
    sentDiv.classList.add('d-none');

    try {
      const formData = new FormData(form);

      const response = await fetch(form.action, {
        method: 'POST',
        body: formData
      });

      const result = await response.json();

      loadingDiv.classList.add('d-none');

      if (result.status === 'success') {
        sentDiv.classList.remove('d-none');
        form.reset(); // Réinitialiser le formulaire
      } else {
        errorDiv.textContent = result.message;
        errorDiv.style.display = 'block';
      }

    } catch (error) {
      loadingDiv.classList.add('d-none');
      errorDiv.textContent = "Une erreur est survenue. Veuillez réessayer.";
      errorDiv.style.display = 'block';
    }
  });
});