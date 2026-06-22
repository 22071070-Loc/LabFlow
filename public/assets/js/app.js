document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.alert').forEach((alert) => {
    setTimeout(() => alert.style.opacity = '0.92', 2000);
  });
});
