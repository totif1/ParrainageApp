import { Component } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { CommonModule } from '@angular/common';
import { ApiService } from '../../services/api.service';
import { InscriptionRequest } from '../../models/inscription.model';

@Component({
  selector: 'app-registration',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  template: `
    <div class="container mt-4">
      <div class="row justify-content-center">
        <div class="col-lg-6">

          <!-- Titre -->
          <div class="text-center mb-4">
            <h1 class="display-5 fw-bold text-primary">📝 Inscription au Parrainage</h1>
            <p class="lead text-muted">Rejoignez notre communauté d'entraide !</p>
          </div>

          <!-- Formulaire -->
          <div class="card shadow-sm">
            <div class="card-body p-4">
              <form [formGroup]="registrationForm" (ngSubmit)="onSubmit()">

                <!-- Prénom -->
                <div class="mb-3">
                  <label for="prenom" class="form-label fw-bold">
                    <i class="bi bi-person"></i> Prénom *
                  </label>
                  <input
                    type="text"
                    id="prenom"
                    class="form-control"
                    formControlName="prenom"
                    [class.is-invalid]="registrationForm.get('prenom')?.invalid && registrationForm.get('prenom')?.touched"
                    placeholder="Votre prénom">
                  <div class="invalid-feedback"
                       *ngIf="registrationForm.get('prenom')?.invalid && registrationForm.get('prenom')?.touched">
                    Le prénom est requis (minimum 2 caractères).
                  </div>
                </div>

                <!-- Nom -->
                <div class="mb-3">
                  <label for="nom" class="form-label fw-bold">
                    <i class="bi bi-person-badge"></i> Nom *
                  </label>
                  <input
                    type="text"
                    id="nom"
                    class="form-control"
                    formControlName="nom"
                    [class.is-invalid]="registrationForm.get('nom')?.invalid && registrationForm.get('nom')?.touched"
                    placeholder="Votre nom de famille">
                  <div class="invalid-feedback"
                       *ngIf="registrationForm.get('nom')?.invalid && registrationForm.get('nom')?.touched">
                    Le nom est requis (minimum 2 caractères).
                  </div>
                </div>

                <!-- Email -->
                <div class="mb-3">
                  <label for="email" class="form-label fw-bold">
                    <i class="bi bi-envelope"></i> Email *
                  </label>
                  <input
                    type="email"
                    id="email"
                    class="form-control"
                    formControlName="email"
                    [class.is-invalid]="registrationForm.get('email')?.invalid && registrationForm.get('email')?.touched"
                    placeholder="votre.email@etudiant.univ.fr">
                  <div class="invalid-feedback"
                       *ngIf="registrationForm.get('email')?.invalid && registrationForm.get('email')?.touched">
                    Veuillez saisir une adresse email valide.
                  </div>
                </div>

                <!-- discord -->
                <div class="mb-3">
                  <label for="discord" class="form-label fw-bold">
                    <i class="bi bi-envelope"></i> discord
                  </label>
                  <input
                    type="text"
                    id="discord"
                    class="form-control"
                    formControlName="discord"
                    [class.is-invalid]="registrationForm.get('discord')?.invalid && registrationForm.get('discord')?.touched"
                    placeholder="dream#1234">
                </div>

                <!-- insta -->
                <div class="mb-3">
                  <label for="insta" class="form-label fw-bold">
                    <i class="bi bi-envelope"></i> insta
                  </label>
                  <input
                    type="text"
                    id="insta"
                    class="form-control"
                    formControlName="insta"
                    [class.is-invalid]="registrationForm.get('insta')?.invalid && registrationForm.get('insta')?.touched"
                    placeholder="insta">
                </div>

                <!-- Classe -->
                <div class="mb-3">
                  <label for="classe" class="form-label fw-bold">
                    <i class="bi bi-mortarboard"></i> Niveau d'étude *
                  </label>
                  <select
                    id="classe"
                    class="form-select"
                    formControlName="classe"
                    [class.is-invalid]="registrationForm.get('classe')?.invalid && registrationForm.get('classe')?.touched">
                    <option value="">-- Sélectionnez votre niveau --</option>
                    <option value="BUT1">BUT1 - Première année (cherche un parrain/marraine)</option>
                    <option value="BUT2">BUT2 - Deuxième année (peut être parrain/marraine)</option>
                    <option value="BUT3">BUT3 - Troisième année (peut être parrain/marraine)</option>
                  </select>
                  <div class="invalid-feedback"
                       *ngIf="registrationForm.get('classe')?.invalid && registrationForm.get('classe')?.touched">
                    Veuillez sélectionner votre niveau d'étude.
                  </div>
                </div>

                <!-- Motivation -->
                <div class="mb-4">
                  <label for="motivation" class="form-label fw-bold">
                    <i class="bi bi-chat-heart"></i> Motivation / Remarques
                  </label>
                  <textarea
                    id="motivation"
                    class="form-control"
                    formControlName="motivation"
                    rows="4"
                    placeholder="Parlez-nous de vos attentes, de ce que vous pouvez apporter ou de toute information utile..."></textarea>
                  <div class="form-text">
                    Optionnel - Cela nous aidera à mieux vous apparier !
                  </div>
                </div>

                <!-- Messages d'erreur/succès -->
                <div class="alert alert-success" *ngIf="successMessage">
                  <i class="bi bi-check-circle"></i> {{ successMessage }}
                </div>

                <div class="alert alert-danger" *ngIf="errorMessage">
                  <i class="bi bi-exclamation-triangle"></i> {{ errorMessage }}
                </div>

                <!-- Boutons -->
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                  <button
                    type="button"
                    class="btn btn-secondary me-md-2"
                    (click)="goBack()">
                    <i class="bi bi-arrow-left"></i>
                    Retour
                  </button>
                  <button
                    type="submit"
                    class="btn btn-primary"
                    [disabled]="registrationForm.invalid || isSubmitting">
                    <span *ngIf="isSubmitting" class="spinner-border spinner-border-sm me-2" role="status"></span>
                    <i class="bi bi-check-lg" *ngIf="!isSubmitting"></i>
                    {{ isSubmitting ? 'Inscription en cours...' : 'S\'inscrire' }}
                  </button>
                </div>

              </form>
            </div>
          </div>

          <!-- Information supplémentaire -->
          <div class="mt-4 p-3 bg-light rounded">
            <h6 class="fw-bold mb-2">ℹ️ Prochaines étapes :</h6>
            <ol class="small mb-0">
              <li>Validation de votre inscription par l'équipe pédagogique</li>
              <li>Mise en relation avec votre parrain/filleul</li>
              <li>Premier contact par email dans les 48h</li>
            </ol>
          </div>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .card {
      border: none;
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .form-control:focus, .form-select:focus {
      border-color: #0d6efd;
      box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    .btn-primary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none;
    }

    .btn-primary:hover {
      background: linear-gradient(135deg, #5a67d8 0%, #6b46a3 100%);
      transform: translateY(-1px);
    }

    .alert {
      border: none;
      border-radius: 0.5rem;
    }
  `]
})
export class RegistrationComponent {
  registrationForm: FormGroup;
  isSubmitting = false;
  successMessage = '';
  errorMessage = '';

  constructor(
    private fb: FormBuilder,
    private apiService: ApiService,
    private router: Router
  ) {
    this.registrationForm = this.fb.group({
      prenom: ['', [Validators.required, Validators.minLength(2)]],
      nom: ['', [Validators.required, Validators.minLength(2)]],
      email: ['', [Validators.required, Validators.email]],
      classe: ['', [Validators.required]],
      discord: [''],
      insta:[''],
      motivation: [''],
    });
  }

  onSubmit(): void {
    if (this.registrationForm.valid) {
      this.isSubmitting = true;
      this.successMessage = '';
      this.errorMessage = '';

      const inscriptionData: InscriptionRequest = {
        nom: this.registrationForm.value.nom.trim(),
        prenom: this.registrationForm.value.prenom.trim(),
        email: this.registrationForm.value.email.trim().toLowerCase(),
        classe: this.registrationForm.value.classe,
        motivation: this.registrationForm.value.motivation.trim(),
        discord: this.registrationForm.value.discord.trim(),
        insta: this.registrationForm.value.insta.trim(),

      };

      this.apiService.createInscription(inscriptionData).subscribe({
        next: (response) => {
          this.isSubmitting = false;
          if (response.success) {
            this.successMessage = 'Inscription réussie ! Vous recevrez bientôt un email de confirmation.';
            this.registrationForm.reset();
            // Redirection automatique après 3 secondes
            setTimeout(() => {
              this.router.navigate(['/']);
            }, 3000);
          } else {
            this.errorMessage = response.message || 'Une erreur est survenue lors de l\'inscription.';
          }
        },
        error: (error) => {
          this.isSubmitting = false;
          console.error('Erreur lors de l\'inscription:', error);
          this.errorMessage = 'Erreur de connexion. Veuillez réessayer plus tard.';
        }
      });
    } else {
      // Marquer tous les champs comme touchés pour afficher les erreurs
      Object.keys(this.registrationForm.controls).forEach(key => {
        this.registrationForm.get(key)?.markAsTouched();
      });
    }
  }

  goBack(): void {
    this.router.navigate(['/']);
  }
}
