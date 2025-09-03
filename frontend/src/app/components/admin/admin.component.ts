import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { CommonModule } from '@angular/common';
import { ApiService } from '../../services/api.service';
import { Inscription, LoginRequest } from '../../models/inscription.model';

// Déclaration pour Bootstrap
declare var bootstrap: any;

@Component({
  selector: 'app-admin',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  template: `
    <div class="container mt-4">

      <!-- Page de connexion -->
      <div class="row justify-content-center" *ngIf="!isAuthenticated">
        <div class="col-md-6 col-lg-4">
          <div class="card shadow-sm">
            <div class="card-header bg-primary text-white text-center">
              <h4 class="mb-0">
                <i class="bi bi-shield-lock"></i> Accès Administrateur
              </h4>
            </div>
            <div class="card-body p-4">
              <form [formGroup]="loginForm" (ngSubmit)="onLogin()">

                <div class="mb-3">
                  <label for="username" class="form-label">Nom d'utilisateur</label>
                  <input
                    type="text"
                    id="username"
                    class="form-control"
                    formControlName="username"
                    placeholder="admin"
                    [class.is-invalid]="loginForm.get('username')?.invalid && loginForm.get('username')?.touched">
                  <div class="invalid-feedback"
                       *ngIf="loginForm.get('username')?.invalid && loginForm.get('username')?.touched">
                    Le nom d'utilisateur est requis.
                  </div>
                </div>

                <div class="mb-3">
                  <label for="password" class="form-label">Mot de passe</label>
                  <input
                    type="password"
                    id="password"
                    class="form-control"
                    formControlName="password"
                    placeholder="••••••••"
                    [class.is-invalid]="loginForm.get('password')?.invalid && loginForm.get('password')?.touched">
                  <div class="invalid-feedback"
                       *ngIf="loginForm.get('password')?.invalid && loginForm.get('password')?.touched">
                    Le mot de passe est requis.
                  </div>
                </div>

                <div class="alert alert-danger" *ngIf="loginError">
                  <i class="bi bi-exclamation-triangle"></i> {{ loginError }}
                </div>

                <div class="d-grid gap-2">
                  <button
                    type="submit"
                    class="btn btn-primary"
                    [disabled]="loginForm.invalid || isLoggingIn">
                    <span *ngIf="isLoggingIn" class="spinner-border spinner-border-sm me-2" role="status"></span>
                    <i class="bi bi-box-arrow-in-right" *ngIf="!isLoggingIn"></i>
                    {{ isLoggingIn ? 'Connexion...' : 'Se connecter' }}
                  </button>
                  <button type="button" class="btn btn-outline-secondary" (click)="goHome()">
                    <i class="bi bi-house"></i> Accueil
                  </button>
                </div>
              </form>

              <div class="mt-3 p-2 bg-light rounded small">
                <strong>Identifiants par défaut :</strong><br>
                Utilisateur: <code>admin</code><br>
                Mot de passe: <code>admin123</code>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Page administrateur -->
      <div *ngIf="isAuthenticated">

        <!-- Header admin -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h1 class="display-6 fw-bold text-primary">
              <i class="bi bi-gear"></i> Administration
            </h1>
            <p class="text-muted mb-0">Gestion des inscriptions au parrainage</p>
          </div>
          <div>
            <button type="button" class="btn btn-outline-primary me-2" (click)="refreshData()">
              <i class="bi bi-arrow-clockwise"></i> Actualiser
            </button>
            <button type="button" class="btn btn-outline-danger" (click)="onLogout()">
              <i class="bi bi-box-arrow-right"></i> Déconnexion
            </button>
          </div>
        </div>

        <!-- Statistiques -->
        <div class="row mb-4">
          <div class="col-md-3">
            <div class="card bg-primary text-white">
              <div class="card-body text-center">
                <h3 class="mb-1">{{ getTotalInscriptions() }}</h3>
                <p class="mb-0 small">Total inscriptions</p>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card bg-success text-white">
              <div class="card-body text-center">
                <h3 class="mb-1">{{ getInscriptionsByClass('BUT1').length }}</h3>
                <p class="mb-0 small">BUT1 (Filleuls)</p>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card bg-info text-white">
              <div class="card-body text-center">
                <h3 class="mb-1">{{ getInscriptionsByClass('BUT2').length }}</h3>
                <p class="mb-0 small">BUT2 (Parrains)</p>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card bg-warning text-white">
              <div class="card-body text-center">
                <h3 class="mb-1">{{ getInscriptionsByClass('BUT3').length }}</h3>
                <p class="mb-0 small">BUT3 (Parrains)</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Filtre par classe -->
        <div class="mb-3">
          <div class="d-flex flex-wrap gap-2">
            <button
              type="button"
              class="btn btn-outline-primary"
              [class.active]="selectedFilter === 'all'"
              (click)="setFilter('all')">
              Toutes les classes ({{ getTotalInscriptions() }})
            </button>
            <button
              type="button"
              class="btn btn-outline-success"
              [class.active]="selectedFilter === 'BUT1'"
              (click)="setFilter('BUT1')">
              BUT1 ({{ getInscriptionsByClass('BUT1').length }})
            </button>
            <button
              type="button"
              class="btn btn-outline-info"
              [class.active]="selectedFilter === 'BUT2'"
              (click)="setFilter('BUT2')">
              BUT2 ({{ getInscriptionsByClass('BUT2').length }})
            </button>
            <button
              type="button"
              class="btn btn-outline-warning"
              [class.active]="selectedFilter === 'BUT3'"
              (click)="setFilter('BUT3')">
              BUT3 ({{ getInscriptionsByClass('BUT3').length }})
            </button>
          </div>
        </div>

        <!-- Loading -->
        <div class="text-center p-4" *ngIf="isLoading">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Chargement...</span>
          </div>
        </div>

        <!-- Message d'erreur -->
        <div class="alert alert-danger" *ngIf="errorMessage">
          <i class="bi bi-exclamation-triangle"></i> {{ errorMessage }}
        </div>

        <!-- Tableau des inscriptions -->
        <div class="card shadow-sm" *ngIf="!isLoading && inscriptions.length > 0">
          <div class="card-header">
            <h5 class="mb-0">
              <i class="bi bi-table"></i>
              Liste des inscriptions
              <span class="badge bg-secondary ms-2">{{ getFilteredInscriptions().length }}</span>
            </h5>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead class="table-light">
                <tr>
                  <th scope="col">#</th>
                  <th scope="col">Nom</th>
                  <th scope="col">Prénom</th>
                  <th scope="col">Email</th>
                  <th scope="col">Classe</th>
                  <th scope="col">Date d'inscription</th>
                  <th scope="col">Action</th>
                </tr>
                </thead>
                <tbody>
                <tr *ngFor="let inscription of getFilteredInscriptions(); trackBy: trackByInscription">
                  <th scope="row">{{ inscription.id }}</th>
                  <td>{{ inscription.nom }}</td>
                  <td>{{ inscription.prenom }}</td>
                  <td>
                    <a [href]="'mailto:' + inscription.email" class="text-decoration-none">
                      {{ inscription.email }}
                    </a>
                  </td>
                  <td>
                      <span
                        class="badge"
                        [class.bg-success]="inscription.classe === 'BUT1'"
                        [class.bg-info]="inscription.classe === 'BUT2'"
                        [class.bg-warning]="inscription.classe === 'BUT3'">
                        {{ inscription.classe }}
                      </span>
                  </td>
                  <td>{{ formatDate(inscription.date_inscription) }}</td>
                  <td>
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-info"
                      (click)="showDetails(inscription)">
                      <i class="bi bi-eye"></i> Détails
                    </button>
                  </td>
                </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Message si aucune inscription -->
        <div class="text-center p-5" *ngIf="!isLoading && inscriptions.length === 0">
          <i class="bi bi-inbox display-1 text-muted"></i>
          <p class="mt-3 text-muted">Aucune inscription trouvée.</p>
        </div>
      </div>

      <!-- Modal des détails -->
      <div class="modal fade" id="detailsModal" tabindex="-1" *ngIf="selectedInscription">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="bi bi-person-badge"></i>
                Détails de l'inscription
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <dl class="row">
                <dt class="col-sm-4">ID :</dt>
                <dd class="col-sm-8">{{ selectedInscription.id }}</dd>

                <dt class="col-sm-4">Nom complet :</dt>
                <dd class="col-sm-8">{{ selectedInscription.prenom }} {{ selectedInscription.nom }}</dd>

                <dt class="col-sm-4">Email :</dt>
                <dd class="col-sm-8">
                  <a [href]="'mailto:' + selectedInscription.email">{{ selectedInscription.email }}</a>
                </dd>

                <dt class="col-sm-4">Classe :</dt>
                <dd class="col-sm-8">
                  <span
                    class="badge"
                    [class.bg-success]="selectedInscription.classe === 'BUT1'"
                    [class.bg-info]="selectedInscription.classe === 'BUT2'"
                    [class.bg-warning]="selectedInscription.classe === 'BUT3'">
                    {{ selectedInscription.classe }}
                  </span>
                </dd>

                <dt class="col-sm-4">Date :</dt>
                <dd class="col-sm-8">{{ formatDate(selectedInscription.date_inscription) }}</dd>

                <dt class="col-sm-4">Motivation :</dt>
                <dd class="col-sm-8">
                  <div class="p-2 bg-light rounded" *ngIf="selectedInscription.motivation; else noMotivation">
                    {{ selectedInscription.motivation }}
                  </div>
                  <ng-template #noMotivation>
                    <em class="text-muted">Aucune motivation renseignée</em>
                  </ng-template>
                </dd>
              </dl>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .card {
      border: none;
      box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .table th {
      font-weight: 600;
      font-size: 0.875rem;
    }

    .badge {
      font-size: 0.75rem;
    }

    .btn.active {
      background-color: var(--bs-primary);
      color: white;
      border-color: var(--bs-primary);
    }

    .modal-header {
      background-color: #f8f9fa;
      border-bottom: 1px solid #dee2e6;
    }
  `]
})
export class AdminComponent implements OnInit {
  loginForm: FormGroup;
  inscriptions: Inscription[] = [];
  selectedInscription: Inscription | null = null;
  selectedFilter = 'all';

  isAuthenticated = false;
  isLoading = false;
  isLoggingIn = false;
  loginError = '';
  errorMessage = '';

  constructor(
    private fb: FormBuilder,
    private apiService: ApiService,
    private router: Router
  ) {
    this.loginForm = this.fb.group({
      username: ['', [Validators.required]],
      password: ['', [Validators.required]]
    });
  }

  ngOnInit(): void {
    this.isAuthenticated = this.apiService.isAuthenticated();
    if (this.isAuthenticated) {
      this.loadInscriptions();
    }

    // Observer les changements d'authentification
    this.apiService.isAuthenticated$.subscribe(isAuth => {
      this.isAuthenticated = isAuth;
      if (isAuth) {
        this.loadInscriptions();
      }
    });
  }

  onLogin(): void {
    if (this.loginForm.valid) {
      this.isLoggingIn = true;
      this.loginError = '';

      const credentials: LoginRequest = {
        username: this.loginForm.value.username,
        password: this.loginForm.value.password
      };

      this.apiService.login(credentials).subscribe({
        next: (response) => {
          this.isLoggingIn = false;
          if (response.success) {
            this.isAuthenticated = true;
            this.loadInscriptions();
          } else {
            this.loginError = response.message || 'Identifiants incorrects';
          }
        },
        error: (error) => {
          this.isLoggingIn = false;
          console.error('Erreur de connexion:', error);
          this.loginError = 'Erreur de connexion au serveur';
        }
      });
    }
  }

  onLogout(): void {
    this.apiService.logout();
    this.isAuthenticated = false;
    this.inscriptions = [];
    this.selectedFilter = 'all';
    this.loginForm.reset();
  }

  loadInscriptions(): void {
    this.isLoading = true;
    this.errorMessage = '';

    this.apiService.getInscriptions().subscribe({
      next: (response) => {
        this.isLoading = false;
        if (response.success && response.data) {
          this.inscriptions = response.data;
        } else {
          this.errorMessage = response.message || 'Erreur lors du chargement des données';
        }
      },
      error: (error) => {
        this.isLoading = false;
        console.error('Erreur lors du chargement:', error);
        this.errorMessage = 'Erreur de connexion au serveur';
      }
    });
  }

  refreshData(): void {
    this.loadInscriptions();
  }

  setFilter(filter: string): void {
    this.selectedFilter = filter;
  }

  getFilteredInscriptions(): Inscription[] {
    if (this.selectedFilter === 'all') {
      return this.inscriptions;
    }
    return this.inscriptions.filter(inscription => inscription.classe === this.selectedFilter);
  }

  getInscriptionsByClass(classe: string): Inscription[] {
    return this.inscriptions.filter(inscription => inscription.classe === classe);
  }

  getTotalInscriptions(): number {
    return this.inscriptions.length;
  }

  showDetails(inscription: Inscription): void {
    this.selectedInscription = inscription;
    // Utiliser Bootstrap modal
    setTimeout(() => {
      const modalElement = document.getElementById('detailsModal');
      if (modalElement && bootstrap) {
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
      }
    }, 0);
  }

  formatDate(dateString?: string): string {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  }

  trackByInscription(index: number, inscription: Inscription): number {
    return inscription.id || index;
  }

  goHome(): void {
    this.router.navigate(['/']);
  }
}
