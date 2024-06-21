export class Utils {
    static formatLargeNumber(number: number): string {
        let suffix = '';
        if (number >= 1000) {
            suffix = 'k';
            number = number / 1000;
        }
        if (number >= 1000) {
            suffix = 'm';
            number = number / 1000;
        }
        if (number >= 1000) {
            suffix = 'b';
            number = number / 1000;
        }

        return Math.floor(number).toLocaleString() + suffix;
    }

    static relativeDate(postDate: Date | number | string): string {
        let postTimestamp: number;

        if (typeof postDate === 'number') {
            postTimestamp = postDate;
        } else {
            postTimestamp = new Date(postDate).getTime();
        }

        const now = Date.now();
        const timeDifference = now - postTimestamp;

        const minute = 60 * 1000;
        const hour = minute * 60;
        const day = hour * 24;
        const month = day * 30;
        const year = day * 365;

        if (timeDifference < minute) {
            return "à l'instant";
        } else if (timeDifference < hour) {
            const minutesAgo = Math.floor(timeDifference / minute);
            return `il y a ${minutesAgo} minute${minutesAgo > 1 ? 's' : ''}`;
        } else if (timeDifference < day) {
            const hoursAgo = Math.floor(timeDifference / hour);
            return `il y a ${hoursAgo} heure${hoursAgo > 1 ? 's' : ''}`;
        } else if (timeDifference < month) {
            const daysAgo = Math.floor(timeDifference / day);
            return `il y a ${daysAgo} jour${daysAgo > 1 ? 's' : ''}`;
        } else if (timeDifference < year) {
            const monthsAgo = Math.floor(timeDifference / month);
            return `il y a ${monthsAgo} mois`;
        } else {
            const yearsAgo = Math.floor(timeDifference / year);
            return `il y a ${yearsAgo} an${yearsAgo > 1 ? 's' : ''}`;
        }
    }

    static displayUsername(username: string): string{
        return '@' + username;
    }

    static remainTime(datetime: string, hours: number): number {
        const inputDate = new Date(datetime);
        const now = new Date();
        
        inputDate.setHours(inputDate.getHours() + hours);
        
        const timeDifference = inputDate.getTime() - now.getTime();
        
        const hoursDifference = Math.floor(timeDifference / (1000 * 60 * 60));
    
        return hoursDifference;
    }

}