export class User {
    constructor(public username: string, 
        public password: string, public email?: string,
        public firstname?: string, public lastname?: string,
        public phone?: string) {
        this.username = username;
        this.password = password;
        this.email = email;
        this.firstname = firstname;
        this.lastname = lastname;
        this.phone = phone;
    }
}