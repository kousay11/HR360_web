document.addEventListener('DOMContentLoaded', function() {
  // Gestion des onglets
  const tabs = document.querySelectorAll('.auth-tab');
  const forms = document.querySelectorAll('.auth-form');
  
  tabs.forEach(tab => {
      tab.addEventListener('click', function() {
          // Désactiver tous les onglets et formulaires
          tabs.forEach(t => t.classList.remove('active'));
          forms.forEach(f => f.classList.remove('active'));
          
          // Activer l'onglet et le formulaire sélectionnés
          this.classList.add('active');
          const formId = this.getAttribute('data-tab') + '-form';
          document.getElementById(formId).classList.add('active');
      });
  });
  
  // Gestion de la soumission du formulaire d'inscription
  const registerForm = document.getElementById('register-form');
  const successMessage = document.querySelector('.auth-success');
  
  if (registerForm) {
      registerForm.addEventListener('submit', function(e) {
          e.preventDefault();
          
          // Ici vous ajouteriez votre logique de validation et d'envoi
          
          // Simulation de succès
          forms.forEach(f => f.classList.remove('active'));
          successMessage.style.display = 'block';
          
          // En production, vous utiliseriez une requête AJAX ou laisseriez le formulaire se soumettre normalement
          // this.submit();
      });
  }
  
  // Gestion du mot de passe oublié
  const forgotPassword = document.querySelector('.forgot-password');
  if (forgotPassword) {
      forgotPassword.addEventListener('click', function(e) {
          e.preventDefault();
          alert('Fonctionnalité "Mot de passe oublié" à implémenter');
      });
  }
});