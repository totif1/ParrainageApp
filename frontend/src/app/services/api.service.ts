import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders, HttpErrorResponse } from '@angular/common/http';
import { Observable, BehaviorSubject, throwError } from 'rxjs';
import { map, catchError } from 'rxjs/operators';
import { Inscription, InscriptionRequest, ApiResponse, LoginRequest, LoginResponse } from '../models/inscription.model';

@Injectable({
  providedIn: 'root'
})
export class ApiService {
  // CORRECTION : Supprimer le /api du baseUrl
  private baseUrl = 'http://localhost:8081';
  private isAuthenticatedSubject = new BehaviorSubject<boolean>(false);
  public isAuthenticated$ = this.isAuthenticatedSubject.asObservable();

  constructor(private http: HttpClient) {
    // Vérifier si l'utilisateur est déjà connecté
    const token = localStorage.getItem('admin_token');
    this.isAuthenticatedSubject.next(!!token);

    console.log('🔧 ApiService initialisé avec baseUrl:', this.baseUrl);
  }

  // Inscription d'un étudiant
  createInscription(inscription: InscriptionRequest): Observable<ApiResponse<Inscription>> {
    console.log('📝 Création inscription:', inscription);

    return this.http.post<ApiResponse<Inscription>>(`${this.baseUrl}/inscriptions`, inscription)
      .pipe(
        catchError(this.handleError)
      );
  }

  // Connexion admin
  login(credentials: LoginRequest): Observable<LoginResponse> {
    console.log('🔐 Tentative de connexion vers:', `${this.baseUrl}/auth/login`);
    console.log('🔐 Credentials:', { username: credentials.username, password: '***' });

    return this.http.post<LoginResponse>(`${this.baseUrl}/auth/login`, credentials, {
      headers: {
        'Content-Type': 'application/json'
      }
    }).pipe(
      map(response => {
        console.log('✅ Réponse login:', response);

        if (response.success && response.token) {
          localStorage.setItem('admin_token', response.token);
          this.isAuthenticatedSubject.next(true);
          console.log('✅ Token stocké');
        }
        return response;
      }),
      catchError(this.handleError)
    );
  }

  // Déconnexion
  logout(): void {
    console.log('🚪 Déconnexion...');
    localStorage.removeItem('admin_token');
    this.isAuthenticatedSubject.next(false);
  }

  // Récupérer toutes les inscriptions (admin uniquement)
  getInscriptions(): Observable<ApiResponse<Inscription[]>> {
    console.log('📋 Récupération des inscriptions...');

    const headers = this.getAuthHeaders();
    return this.http.get<ApiResponse<Inscription[]>>(`${this.baseUrl}/inscriptions`, { headers })
      .pipe(
        catchError(this.handleError)
      );
  }

  delete(id: number): Observable<ApiResponse<{}>> {
    console.log('🗑️ Suppression inscription ID:', id);

    const headers = this.getAuthHeaders();
    return this.http.delete<ApiResponse<{}>>(`${this.baseUrl}/inscriptions/${id}`, { headers })
      .pipe(
        catchError(this.handleError)
      );
  }
  // Vérifier si l'utilisateur est connecté
  isAuthenticated(): boolean {
    const token = localStorage.getItem('admin_token');
    return !!token;
  }

  // Test de connectivité
  testConnection(): Observable<any> {
    console.log('🧪 Test de connectivité vers:', `${this.baseUrl}/health`);

    return this.http.get(`${this.baseUrl}/health`)
      .pipe(
        catchError(this.handleError)
      );
  }

  // Headers avec token d'authentification
  private getAuthHeaders(): HttpHeaders {
    const token = localStorage.getItem('admin_token');
    return new HttpHeaders({
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    });
  }

  // Gestion des erreurs HTTP
  private handleError = (error: HttpErrorResponse) => {
    console.error('❌ Erreur HTTP:', error);

    let errorMessage = 'Une erreur est survenue';

    if (error.error instanceof ErrorEvent) {
      // Erreur côté client
      errorMessage = `Erreur client: ${error.error.message}`;
      console.error('Erreur côté client:', error.error.message);
    } else {
      // Erreur côté serveur
      console.error(`Code d'erreur: ${error.status}`);
      console.error(`Message d'erreur:`, error.error);

      if (error.status === 0) {
        errorMessage = 'Impossible de joindre le serveur. Vérifiez que l\'API fonctionne sur http://localhost:8080';
      } else if (error.status === 401) {
        errorMessage = 'Identifiants incorrects';
        // Déconnexion automatique si token invalide
        this.logout();
      } else if (error.status === 404) {
        errorMessage = 'Endpoint non trouvé. Vérifiez l\'URL de l\'API.';
      } else if (error.error?.message) {
        errorMessage = error.error.message;
      } else {
        errorMessage = `Erreur ${error.status}: ${error.statusText}`;
      }
    }

    return throwError(() => errorMessage);
  }


}
