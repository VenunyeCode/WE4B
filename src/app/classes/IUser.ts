export interface IUser {
    id_user: number;
    username: string;
    email: string;
    firstname: string;
    lastname: string;
    avatar: string;
    banned_temporarly: number;
    interdiction_date: string | null;
    role: string;
    self_intro?:string;
    address?:string;
    phone?:string;
    password?:string;
  }