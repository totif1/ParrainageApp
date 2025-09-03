import { Component } from '@angular/core';
import { Router } from '@angular/router';

@Component({
  selector: 'app-home',
  template: `
    <div class="container mt-5">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <!-- Hero Section -->
          <div class="jumbotron bg-primary text-white p-5 rounded mb-5">
            <h1 class="display-4 fw-bold">🎓 Système de Parrainage BUT Informatique</h1>
            <p class="lead">Connectons les générations d'étudiants pour un parcours réussi !</p>
          </div>

          <!-- Description du principe -->
          <div class="card shadow-sm mb-5">
            <div class="card-body p-4">
              <h2 class="card-title text-primary mb-4">
                <i class="bi bi-people-fill"></i> Le principe du parrainage
              </h2>

              <div class="row">
                <div class="col-md-6">
                  <h5 class="text-success">👥 Pour les parrains/marraines (BUT2/BUT3)</h5>
                  <ul class="list-unstyled">
                    <li class="mb-2">✅ Partager votre expérience</li>
                    <li class="mb-2">✅ Aider les nouveaux étudiants</li>
                    <li class="mb-2">✅ Développer vos compétences de mentorat</li>
                    <li class="mb-2">✅ Créer des liens durables</li>
                  </ul>
                </div>

                <div class="col-md-6">
                  <h5 class="text-info">🌟 Pour les filleuls (BUT1)</h5>
                  <ul class="list-unstyled">
                    <li class="mb-2">✅ Être accompagné dans vos débuts</li>
                    <li class="mb-2">✅ Recevoir des conseils pratiques</li>
                    <li class="mb-2">✅ Éviter les pièges courants</li>
                    <li class="mb-2">✅ Intégrer plus facilement</li>
                  </ul>
                </div>
              </div>

              <div class="alert alert-light mt-4">
                <strong>💡 Comment ça marche ?</strong><br>
                Inscrivez-vous via le formulaire ci-dessous en précisant votre niveau d'étude.
                Nous vous mettrons en relation avec un parrain ou un filleul selon votre profil !
              </div>
            </div>
          </div>

          <!-- Call to Action -->
          <div class="text-center mb-5">
            <button
              class="btn btn-success btn-lg px-5 py-3"
              (click)="goToRegistration()">
              <i class="bi bi-person-plus-fill"></i>
              Devenir parrain/filleul
            </button>
          </div>

          <!-- Statistiques -->
          <div class="row text-center mb-5">
            <div class="col-md-4">
              <div class="card border-0">
                <div class="card-body">
                  <h3 class="text-primary">150+</h3>
                  <p class="text-muted">Étudiants inscrits</p>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card border-0">
                <div class="card-body">
                  <h3 class="text-success">75+</h3>
                  <p class="text-muted">Binômes formés</p>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card border-0">
                <div class="card-body">
                  <h3 class="text-info">3</h3>
                  <p class="text-muted">Années d'expérience</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .jumbotron {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    }

    .card {
      transition: transform 0.2s;
    }

    .card:hover {
      transform: translateY(-5px);
    }

    .btn-success {
      background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
      border: none;
      transition: all 0.3s;
    }

    .btn-success:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 20px rgba(17, 153, 142, 0.3);
    }
  `]
})
export class HomeComponent {
  constructor(private router: Router) {}

  goToRegistration(): void {
    this.router.navigate(['/inscription']);
  }
}
