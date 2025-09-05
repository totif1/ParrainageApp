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
templateUrl: 'app.html',
  styleUrls: ['app.css'],
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
