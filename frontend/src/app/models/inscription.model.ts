export interface Inscription {
  id?: number;
  nom: string;
  prenom: string;
  email: string;
  classe: 'BUT1INFO' | 'BUT2INFO'| 'BUT3INFO'|'BUT1GEA'| 'BUT2GEA'| 'BUT3GEA'|'BTS1AC'| 'BTS2AC';
  motivation?: string;
  date_inscription?: string;
  discord: string;
  insta :string;
  preference : 'PARRAIN' | 'FILLEUL'
}

export interface InscriptionRequest {
  nom: string;
  prenom: string;
  email: string;
  classe: string;
  motivation: string;
  discord: string;
  insta :string;
  preference: string;
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
