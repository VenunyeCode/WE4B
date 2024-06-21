export class Response<T> {
    constructor(
        public status: string,
        public message: string,
        public liked: boolean,
        public likes: number,
        public comments: number,
        public unread_notif: number,
        public views : number,
        public week_views : number,
        public week_likes : number,
        public data_like: T,
        public data_view: T,
        public data: T
    ) { }
}