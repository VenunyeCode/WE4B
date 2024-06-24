import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, throwError, Subject } from 'rxjs';
import { Response } from './classes/ResponseObject';
import { catchError, map } from 'rxjs/operators';
import { SessionService } from './session.service';
import { Post } from './classes/Post';
import { User } from './classes/User';
import { IUser } from './classes/IUser';
import { Notification } from './classes/Notification';
import { Statistics } from './classes/Statistics';
import { AuthResponse } from './auth-response';

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

  getUserPosts(idUser: number): Observable<Response<Post[]>> {
    const url = `${this.apiUrl}/Master.php?f=user_posts&id_user=${idUser}`;
    return this.http.get<Response<Post[]>>(url)
      .pipe(
        map(response => response),
        catchError(this.handleError)
      );
  }

  getUserNotifications(idUser: number): Observable<Response<Notification[]>> {
    const url = `${this.apiUrl}/Master.php?f=user_notif&id_user=${idUser}`;
    return this.http.get<Response<Notification[]>>(url)
      .pipe(
        map(response => response),
        catchError(this.handleError)
      );
  }

  getPostDetail(idPost: number, idUser: number): Observable<Response<Post[]>> {
    const url = `${this.apiUrl}/Master.php?f=post_detail&id_post=${idPost}&id_viewer=${idUser}`;
    return this.http.get<Response<Post[]>>(url)
      .pipe(
        map(response => response),
        catchError(this.handleError)
      );
  }

  getUserInfo(idUser: number, connectedId: number): Observable<Response<IUser>> {
    const url = `${this.apiUrl}/Master.php?f=user_info&id_user=${idUser}&id_connected=${connectedId}`;
    return this.http.get<Response<IUser>>(url)
      .pipe(
        map(response => response),
        catchError(this.handleError)
      );
  }

  getStatistics(idUser: number): Observable<Response<Statistics[]>> {
    const url = `${this.apiUrl}/Users.php?f=stats&id_user=${idUser}`;
    return this.http.get<Response<Statistics[]>>(url)
      .pipe(
        map(response => response),
        catchError(this.handleError)
      );
  }

  getFollowersLikers(idUser: number): Observable<Response<IUser[]>> {
    const url = `${this.apiUrl}/Master.php?f=user_follows&id_user=${idUser}`;
    return this.http.get<Response<IUser[]>>(url)
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

  followUser(following: number, follower: number, status: boolean): Observable<Response<User>> {
    const url = `${this.apiUrl}/Users.php?f=follow`;
    const body = JSON.stringify({ following, follower, status: status ? 1 : 0 });
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

  updateUserInfo(user: IUser, file?: File): Observable<Response<User>> {
    const url = `${this.apiUrl}/Users.php?f=save_member`;
    const formData: FormData = new FormData();
    console.log('LOG DE ID_USER = ', user.id_user.toString());
    formData.append('id_user', user.id_user.toString());
    formData.append('firstname', user.firstname);
    formData.append('lastname', user.lastname);
    formData.append('email', user.email);
    formData.append('phone', <string>user.phone);
    if (user.password != null && user.password.trim().length != 0) {
      formData.append('password', user.password);
    }
    if (user.address != null && user.address.trim().length != 0) {
      formData.append('address', user.address);
    }
    if (user.self_intro != null && user.self_intro.trim().length != 0) {
      formData.append('self_intro', <string>user.self_intro);
    }
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

  getUnreadNotif(userId: number): Observable<Response<User>> {
    const url = `${this.apiUrl}/Users.php?f=num_notif&id_user=${userId}`;
    return this.http.get<Response<User>>(url)
      .pipe(
        map(response => response),
        catchError(this.handleError)
      );
  }

  get_users() : Observable<Array<User>> {
    const url = this.apiUrl + '/Users.php?f=get_all';
    return this.http.get<Array<User>>(url)
      .pipe(
        map(response => response),
        catchError(this.handleError)
      )
  }

  loadImage(filename: string): Observable<Blob> {
    const url = `${this.apiUrl}/Users.php?f=load_image&filename=${filename}`;
    return this.http.get(url, { responseType: 'blob' });
  }

  private handleError(error: any): Observable<never> {
    console.error('An error occurred', error);
    return throwError(error);
  }

  warn_user(username : string) {
    const url = `${this.apiUrl}/Users.php?f=warn_user`;
    const body = JSON.stringify({ username });
    return this.http.post<AuthResponse>(url, body).pipe(
      map(response => response),
      catchError(this.handleError)
    );
  }
}
