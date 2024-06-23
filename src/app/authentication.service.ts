import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { catchError, map } from 'rxjs/operators';
import { User } from './classes/User';
import { AuthResponse } from './auth-response';
import { SessionService } from './session.service';
import { optionsType } from 'ag-charts-community/dist/types/src/chart/mapping/types';

@Injectable({
  providedIn: 'root'
})
export class AuthenticationService {

  private loginUrl = 'http://localhost/WE4B/backend/classes/Login.php?f=user_login';
  private logoutUrl = 'http://localhost/WE4B/backend/classes/Login.php?f=user_logout';
  private registerUrl = 'http://localhost/WE4B/backend/classes/Users.php?f=registration';
  private imageUrl = 'http://localhost/WE4B/backend/classes/Users.php?f=load_image';
  private adminLoginUrl = 'http://localhost/WE4B/backend/classes/Login.php?f=login';
  constructor(private http: HttpClient, private sessionService: SessionService) { }

  login(user: User): Observable<AuthResponse> {
    const url = this.loginUrl;
    const headers = new HttpHeaders({ 'Content-Type': 'application/json' });
    //const body = JSON.stringify({ email, password });

    return this.http.post<AuthResponse>(url, user, { headers })
      .pipe(
        map(response => response),
        catchError(this.handleError)
      );
  }

  login_admin(user : User) : Observable<AuthResponse> {
    const url = this.adminLoginUrl;
    const headers = new HttpHeaders({'Content-Type' : 'application/json'});

    return this.http.post<AuthResponse>(url, user, {headers})
     .pipe(
      map (response => response),
      catchError(this.handleError)

     )
  }

  register(user: User): Observable<AuthResponse> {
    const url = this.registerUrl;
    const headers = new HttpHeaders({ 'Content-Type': 'application/json' });
    //const body = JSON.stringify({ name, email, password });

    return this.http.post<AuthResponse>(url, user, { headers })
      .pipe(
        map(response => response),
        catchError(this.handleError)
      );
  }

  loadImage(filename: string): Observable<Blob>{
    const url = `${this.imageUrl}&filename=${filename}`;
    return this.http.get(url, { responseType: 'blob' });
  }

  logout(userId: number): Observable<AuthResponse> {
    const userData = this.sessionService.get('userdata');
    //const userId = userData ? userData.id_user : null;
    const url = `${this.logoutUrl}&id_user=${userId}`;
    const headers = new HttpHeaders({ 'Content-Type': 'application/json' });
    return this.http.post<AuthResponse>(url, { headers })
      .pipe(
        map(response => response),
        catchError(this.handleError)
      );
  }

  private handleError(error: any): Observable<never> {
    console.error('An error occurred', error);
    return throwError(error);
  }
}
