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
  templateUrl: `admin.component.html`,
  styleUrls: [`admin.component.css`]
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
  delete(inscription: Inscription): void {
    // Demander confirmation
    if (!confirm(`Êtes-vous sûr de vouloir supprimer l'inscription de ${inscription.prenom} ${inscription.nom} ?`)) {
      return;
    }

    if (!inscription.id) {
      console.error('ID de l\'inscription manquant');
      return;
    }

    this.isLoading = true;
    this.errorMessage = '';

    this.apiService.delete(inscription.id).subscribe({
      next: (response) => {
        this.isLoading = false;
        if (response.success) {
          // Supprimer l'inscription de la liste locale
          this.inscriptions = this.inscriptions.filter(i => i.id !== inscription.id);
          console.log('✅ Inscription supprimée avec succès');

        } else {
          this.errorMessage = response.message || 'Erreur lors de la suppression';
        }
      },
      error: (error) => {
        this.isLoading = false;
        console.error('Erreur lors de la suppression:', error);
        this.errorMessage = 'Erreur de connexion au serveur';
      }
    });
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
