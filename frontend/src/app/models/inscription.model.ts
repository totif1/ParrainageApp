export interface Inscription {
  id?: number;
  nom: string;
  prenom: string;
  email: string;
  classe: 'BUT1' | 'BUT2' | 'BUT3';
  motivation?: string;
  date_inscription?: string;
}

export interface InscriptionRequest {
  nom: string;
  prenom: string;
  email: string;
  classe: string;
  motivation: string;
}

export interface ApiResponse<T> {
  success: boolean;
  message?: string;
  data?: T;
}

export interface LoginRequest {
  username: string;
  password: string;
}

export interface LoginResponse {
  success: boolean;
  message?: string;
  token?: string;
}
