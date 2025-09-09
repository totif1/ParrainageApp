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
  preferenceInfo = '';
  preferenceInfoClass = '';
  showPreferenceInfo = false;

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
      insta: [''],
      motivation: [''],
      preference: ['', [Validators.required]]
    });

    // Écouter les changements de classe
    this.registrationForm.get('classe')?.valueChanges.subscribe(classe => {
      this.updatePreference(classe);
    });
  }

  private updatePreference(selectedClasse: string): void {
    const preferenceControl = this.registrationForm.get('preference');

    if (!selectedClasse) {
      preferenceControl?.enable();
      preferenceControl?.setValue('');
      this.showPreferenceInfo = false;
      return;
    }

    // 1ère année : automatiquement filleul
    if (selectedClasse.includes('1')) {
      preferenceControl?.setValue('FILLEUL');
      preferenceControl?.disable();

      this.preferenceInfo = '🎓 Première année : Vous serez automatiquement assigné(e) comme filleul(e) pour bénéficier de l\'accompagnement d\'un étudiant plus expérimenté.';
      this.preferenceInfoClass = 'alert alert-info';
      this.showPreferenceInfo = true;
    }
    // 3ème année (BUT3) ou 2ème année BTS : automatiquement parrain
    else if (selectedClasse.includes('3') || selectedClasse === 'BTS2AC') {
      preferenceControl?.setValue('PARRAIN');
      preferenceControl?.disable();

      this.preferenceInfo = '🏆 Niveau avancé : Vous serez automatiquement assigné(e) comme parrain/marraine pour accompagner un étudiant de première année.';
      this.preferenceInfoClass = 'alert alert-warning';
      this.showPreferenceInfo = true;
    }
    // 2ème année BUT et GEA : libre choix
    else if (selectedClasse.includes('2') && !selectedClasse.includes('BTS')) {
      preferenceControl?.enable();
      preferenceControl?.setValue('');

      this.preferenceInfo = '✨ Deuxième année : Vous pouvez choisir d\'être parrain/marraine ou filleul(e) selon vos préférences.';
      this.preferenceInfoClass = 'alert alert-success';
      this.showPreferenceInfo = true;
    }
  }

  isPreferenceDisabled(): boolean {
    const classe = this.registrationForm.get('classe')?.value;
    return classe?.includes('1') || classe?.includes('3') || classe === 'BTS2AC';
  }

  isParrainDisabled(): boolean {
    const classe = this.registrationForm.get('classe')?.value;
    return classe?.includes('1'); // Seulement les 1ères années ne peuvent pas être parrain
  }

  isFilleulDisabled(): boolean {
    const classe = this.registrationForm.get('classe')?.value;
    return classe?.includes('3') || classe === 'BTS2AC'; // Les 3èmes années et BTS2 ne peuvent pas être filleul
  }

  onSubmit(): void {
    // Réactiver temporairement le contrôle preference pour la validation
    const preferenceControl = this.registrationForm.get('preference');
    const wasDisabled = preferenceControl?.disabled;

    if (wasDisabled) {
      preferenceControl?.enable();
    }

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
        preference: this.registrationForm.value.preference
      };

      this.apiService.createInscription(inscriptionData).subscribe({
        next: (response) => {
          this.isSubmitting = false;
          if (response.success) {
            this.successMessage = 'Inscription réussie ! Vous recevrez bientôt un email de confirmation.';
            this.registrationForm.reset();
            this.showPreferenceInfo = false;
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

    // Remettre le contrôle en état disabled si nécessaire
    if (wasDisabled) {
      preferenceControl?.disable();
    }
  }

  goBack(): void {
    this.router.navigate(['/']);
  }
}
