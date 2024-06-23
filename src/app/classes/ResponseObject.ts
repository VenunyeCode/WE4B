export class Response<T> {
    constructor(
        public status: string,
        public message: string,
        public liked: boolean,
        public likes: number,
        public comments: number,
        public unread_notif: number,
        public views: number,
        public week_views: number,
        public week_likes: number,
        public followers: number,
        public followings: number,
        public is_following: boolean,
        public data_like: T,
        public data_follower: T,
        public data_following: T,
        public data_view: T,
        public data: T
    ) { }
}