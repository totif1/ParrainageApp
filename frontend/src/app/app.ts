import { Component } from '@angular/core';
import { Router, NavigationEnd, RouterLinkActive, RouterLink, RouterOutlet } from '@angular/router';
import { CommonModule } from '@angular/common';
import { ApiService } from './services/api.service';
import { filter } from 'rxjs/operators';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [
    CommonModule,
    RouterLinkActive,
    RouterLink,
    RouterOutlet
  ],
  template: `
    <div class="min-vh-100 bg-light">
      <!-- Navigation -->
      <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
          <a class="navbar-brand fw-bold" routerLink="/">
            <i class="bi bi-mortarboard-fill"></i>
            Parrainage BUT Informatique
          </a>

          <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
          </button>

          <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
              <li class="nav-item">
                <a
                  class="nav-link"
                  routerLink="/"
                  routerLinkActive="active"
                  [routerLinkActiveOptions]="{exact: true}">
                  <i class="bi bi-house"></i> Accueil
                </a>
              </li>
              <li class="nav-item">
                <a
                  class="nav-link"
                  routerLink="/inscription"
                  routerLinkActive="active">
                  <i class="bi bi-person-plus"></i> S'inscrire
                </a>
              </li>
            </ul>

            <ul class="navbar-nav">
              <li class="nav-item" *ngIf="!isAuthenticated">
                <a
                  class="nav-link"
                  routerLink="/admin"
                  routerLinkActive="active">
                  <i class="bi bi-shield-lock"></i> Admin
                </a>
              </li>
              <li class="nav-item dropdown" *ngIf="isAuthenticated">
                <a
                  class="nav-link dropdown-toggle"
                  href="#"
                  role="button"
                  data-bs-toggle="dropdown">
                  <i class="bi bi-person-circle"></i> Administrateur
                </a>
                <ul class="dropdown-menu">
                  <li>
                    <a class="dropdown-item" routerLink="/admin/dashboard">
                      <i class="bi bi-speedometer2"></i> Tableau de bord
                    </a>
                  </li>
                  <li>
                    <hr class="dropdown-divider">
                  </li>
                  <li>
                    <button class="dropdown-item" (click)="logout()">
                      <i class="bi bi-box-arrow-right"></i> Déconnexion
                    </button>
                  </li>
                </ul>
              </li>
            </ul>
          </div>
        </div>
      </nav>

      <!-- Contenu principal -->
      <main class="flex-grow-1">
        <router-outlet></router-outlet>
      </main>

      <!-- Footer -->
      <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
          <div class="row">
            <div class="col-md-6">
              <h6 class="fw-bold">Parrainage BUT Informatique</h6>
              <p class="small mb-0">
                Plateforme d'entraide entre étudiants du BUT Informatique.
                <br>Créons des liens durables pour un parcours réussi !
              </p>
            </div>
            <div class="col-md-3">
              <h6 class="fw-bold">Liens utiles</h6>
              <ul class="list-unstyled small">
                <li><a href="#" class="text-light-emphasis text-decoration-none">Règlement</a></li>
                <li><a href="#" class="text-light-emphasis text-decoration-none">Contact</a></li>
                <li><a href="#" class="text-light-emphasis text-decoration-none">FAQ</a></li>
              </ul>
            </div>
            <div class="col-md-3">
              <h6 class="fw-bold">Contact</h6>
              <p class="small mb-0">
                <i class="bi bi-envelope"></i> parrainage@iut.fr<br>
                <i class="bi bi-telephone"></i> 01 23 45 67 89
              </p>
            </div>
          </div>
          <hr class="my-3">
          <div class="text-center small">
            <p class="mb-0">
              © {{ currentYear }} BUT Informatique - Système de Parrainage |
              <span class="text-muted">Page actuelle: {{ currentRoute }}</span>
            </p>
          </div>
        </div>
      </footer>
    </div>
  `,
  styles: [`
    .navbar-brand {
      font-size: 1.25rem;
    }

    .nav-link.active {
      background-color: rgba(255, 255, 255, 0.1);
      border-radius: 0.375rem;
    }

    .dropdown-menu {
      border: none;
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    footer {
      margin-top: auto;
    }

    .text-light-emphasis {
      color: rgba(255, 255, 255, 0.75) !important;
    }

    .text-light-emphasis:hover {
      color: rgba(255, 255, 255, 0.9) !important;
    }
  `]
})
export class AppComponent {
  title = 'parrainage-but';
  currentYear = new Date().getFullYear();
  currentRoute = '';
  isAuthenticated = false;

  constructor(
    private router: Router,
    private apiService: ApiService
  ) {
    // Suivre les changements de route pour afficher la page actuelle
    this.router.events
      .pipe(filter(event => event instanceof NavigationEnd))
      .subscribe((event) => {
        this.updateCurrentRoute((event as NavigationEnd).url);
      });

    // Observer les changements d'authentification
    this.apiService.isAuthenticated$.subscribe(isAuth => {
      this.isAuthenticated = isAuth;
    });

    // Initialiser l'état d'authentification
    this.isAuthenticated = this.apiService.isAuthenticated();
  }

  private updateCurrentRoute(url: string): void {
    switch (url) {
      case '/':
        this.currentRoute = 'Accueil';
        break;
      case '/inscription':
        this.currentRoute = 'Inscription';
        break;
      case '/admin':
        this.currentRoute = 'Connexion Admin';
        break;
      case '/admin/dashboard':
        this.currentRoute = 'Administration';
        break;
      default:
        this.currentRoute = 'Page inconnue';
    }
  }

  logout(): void {
    this.apiService.logout();
    this.router.navigate(['/']);
  }
}
