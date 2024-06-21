import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, throwError, Subject } from 'rxjs';
import { Response } from './classes/ResponseObject';
import { catchError, map } from 'rxjs/operators';
import { SessionService } from './session.service';
import { Post } from './classes/Post';
import { User } from './classes/User';

@Injectable({
  providedIn: 'root'
})
export class UserService {

  private apiUrl = 'http://localhost/WE4B/backend/classes';

  private refreshSubject = new Subject<void>();

  refresh$ = this.refreshSubject.asObservable();

  triggerRefresh() {
    this.refreshSubject.next();
  }

  constructor(private http: HttpClient) { }

  getLastestPosts(): Observable<Response<Post[]>> {
    const url = `${this.apiUrl}/Master.php?f=lastest_post`;
    return this.http.get<Response<Post[]>>(url)
      .pipe(
        map(response => response),
        catchError(this.handleError)
      );
  }

  getCommentsByPost(idPost: number): Observable<Response<Post[]>> {
    const url = `${this.apiUrl}/Master.php?f=post_comments&id_post=${idPost}`;
    return this.http.get<Response<Post[]>>(url)
      .pipe(
        map(response => response),
        catchError(this.handleError)
      );
  }

  checkPostLike(id_post: number, id_user: number): Observable<Response<User>> {
    const url = `${this.apiUrl}/Master.php?f=check_like`;
    const body = JSON.stringify({ id_post, id_user });
    return this.http.post<Response<User>>(url, body)
      .pipe(
        map(response => response),
        catchError(this.handleError)
      );
  }

  updatePostLike(id_post: number, id_user: number, status: boolean): Observable<Response<User>> {
    const url = `${this.apiUrl}/Master.php?f=update_like`;
    const body = JSON.stringify({ id_post, id_user, status: status ? 1 : 0 });
    return this.http.post<Response<User>>(url, body)
      .pipe(
        map(response => response),
        catchError(this.handleError)
      );
  }

  saveComment(id_post: number, id_user: number, comment: string): Observable<Response<User>> {
    const url = `${this.apiUrl}/Master.php?f=save_comment`;
    const body = JSON.stringify({ id_post, id_user, comment });
    return this.http.post<Response<User>>(url, body)
      .pipe(
        map(response => response),
        catchError(this.handleError)
      );
  }

  savePost(content: string, userId: number, file?: File): Observable<Response<User>> {
    const url = `${this.apiUrl}/Master.php?f=save_post`;
    const formData: FormData = new FormData();
    formData.append('content', content);
    formData.append('id_user', userId.toString());
    if (file) {
      formData.append('img', file, file.name);
    }
    const headers = new HttpHeaders({
      'Accept': 'application/json'
    })
    return this.http.post<Response<User>>(url, formData, { headers })
      .pipe(
        map(response => response),
        catchError(this.handleError)
      );
  }

  getInsight(userId: number): Observable<Response<Post[]>> {
    const url = `${this.apiUrl}/Master.php?f=insight&id_user=${userId}`;
    return this.http.get<Response<Post[]>>(url)
      .pipe(
        map(response => response),
        catchError(this.handleError)
      );
  }

  getUnreadNotif(userId: number): Observable<Response<User>>{
    const url = `${this.apiUrl}/Users.php?f=num_notif&id_user=${userId}`;
    return this.http.get<Response<User>>(url)
      .pipe(
        map(response => response),
        catchError(this.handleError)
      );
  }

  loadImage(filename: string): Observable<Blob> {
    const url = `${this.apiUrl}/Users.php?f=load_image&filename=${filename}`;
    return this.http.get(url, { responseType: 'blob' });
  }

  private handleError(error: any): Observable<never> {
    console.error('An error occurred', error);
    return throwError(error);
  }
}
