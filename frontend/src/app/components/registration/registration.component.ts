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
  templateUrl: `registration.component.html`,
  styleUrls: [`registration.component.css`]
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
