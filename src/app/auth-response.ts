import { User } from "./classes/User";

export class AuthResponse {
    constructor (
        public status: string,
        public message: string,
        public data: any,
    ){
        this.status = status;
        this.message = message;
        this.data = data;
    }
}
