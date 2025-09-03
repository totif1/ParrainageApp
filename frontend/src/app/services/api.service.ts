import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, BehaviorSubject } from 'rxjs';
import { map } from 'rxjs/operators';
import { Inscription, InscriptionRequest, ApiResponse, LoginRequest, LoginResponse } from '../models/inscription.model';

@Injectable({
  providedIn: 'root'
})
export class ApiService {
  private baseUrl = 'http://localhost:8080/api';
  private isAuthenticatedSubject = new BehaviorSubject<boolean>(false);
  public isAuthenticated$ = this.isAuthenticatedSubject.asObservable();

  constructor(private http: HttpClient) {
    // Vérifier si l'utilisateur est déjà connecté
    const token = localStorage.getItem('admin_token');
    this.isAuthenticatedSubject.next(!!token);
  }

  // Inscription d'un étudiant
  createInscription(inscription: InscriptionRequest): Observable<ApiResponse<Inscription>> {
    return this.http.post<ApiResponse<Inscription>>(`${this.baseUrl}/inscriptions`, inscription);
  }

  // Connexion admin
  login(credentials: LoginRequest): Observable<LoginResponse> {
    return this.http.post<LoginResponse>(`${this.baseUrl}/auth/login`, credentials)
      .pipe(
        map(response => {
          if (response.success && response.token) {
            localStorage.setItem('admin_token', response.token);
            this.isAuthenticatedSubject.next(true);
          }
          return response;
        })
      );
  }

  // Déconnexion
  logout(): void {
    localStorage.removeItem('admin_token');
    this.isAuthenticatedSubject.next(false);
  }

  // Récupérer toutes les inscriptions (admin uniquement)
  getInscriptions(): Observable<ApiResponse<Inscription[]>> {
    const headers = this.getAuthHeaders();
    return this.http.get<ApiResponse<Inscription[]>>(`${this.baseUrl}/inscriptions`, { headers });
  }

  // Vérifier si l'utilisateur est connecté
  isAuthenticated(): boolean {
    return !!localStorage.getItem('admin_token');
  }

  // Headers avec token d'authentification
  private getAuthHeaders(): HttpHeaders {
    const token = localStorage.getItem('admin_token');
    return new HttpHeaders({
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    });
  }
}
