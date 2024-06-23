import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class SessionService {

  constructor() { }

  set(key: string, value: any) {
    sessionStorage.setItem(key, JSON.stringify(value));
  }

  get(key: string) {
    const value = sessionStorage.getItem(key);
    return value ? JSON.parse(value) : null;
  }

  clear() {
    sessionStorage.clear();
  }
}
