export default {
    install(app) {
        app.config.globalProperties.$admin = {
            formatNumber: (val) => {
                if (val === null || val === undefined || val === '') return '';
                
                let cleanValue = val.toString().replace(/[^0-9.]/g, '');
                
                const parts = cleanValue.split('.');
                if (parts.length > 2) {
                    cleanValue = parts[0] + '.' + parts.slice(1).join('');
                }
                
                const integerPart = parts[0];
                const decimalPart = parts[1] !== undefined ? '.' + parts[1] : '';
                
                const formattedInteger = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                
                return formattedInteger + decimalPart;
            },

            formatInteger: (val) => {
                if (val === null || val === undefined || val === '') return '';
                
                let cleanValue = val.toString().replace(/[^0-9]/g, '');
                
                return cleanValue.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            },

            /**
             * Generates a formatted price.
             *
             * @param {number} price - The price value to be formatted.
             * @returns {string} - The formatted price string.
             */
            formatPrice: (price) => {
                let locale = 'en-US';

                const currency = JSON.parse(
                    document.querySelector('meta[name="currency"]').content
                );

                const symbol =
                    currency.symbol !== "" ? currency.symbol : currency.code;

                if (!currency.currency_position) {
                    return new Intl.NumberFormat(locale, {
                        style: "currency",
                        currency: currency.code,
                    }).format(price);
                }

                const formatter = new Intl.NumberFormat(locale, {
                    style: "currency",
                    currency: currency.code,
                    minimumFractionDigits: currency.decimal ?? 2,
                });

                const formattedCurrency = formatter
                    .formatToParts(price)
                    .map((part) => {
                        switch (part.type) {
                            case "currency":
                                return "";

                            case "group":
                                return currency.group_separator !== undefined && currency.group_separator !== ""
                                    ? currency.group_separator
                                    : part.value;

                            case "decimal":
                                return currency.decimal_separator !== undefined && currency.decimal_separator !== ""
                                    ? currency.decimal_separator
                                    : part.value;

                            default:
                                return part.value;
                        }
                    })
                    .join("");

                switch (currency.currency_position) {
                    case "left":
                        return symbol + formattedCurrency;

                    case "left_with_space":
                        return symbol + " " + formattedCurrency;

                    case "right":
                        return formattedCurrency + symbol;

                    case "right_with_space":
                        return formattedCurrency + " " + symbol;

                    default:
                        return formattedCurrency;
                }
            },

            /**
             * Generates a formatted date based on specified timezone.
             *
             * @param {string} dateString - The date value to be formatted.
             * @param {string} format - The format to be used for formatting the date.
             * @param {string} timezone - The timezone to use (e.g., 'America/New_York').
             * @returns {string} - The formatted date string.
             */
            formatDate: (dateString, format, timezone) => {
                const date = new Date(dateString);

                const options = { timeZone: timezone };

                const formatter = new Intl.DateTimeFormat("en-US", {
                    ...options,
                    hour12: false,
                    year: "numeric",
                    month: "numeric",
                    day: "numeric",
                    hour: "numeric",
                    minute: "numeric",
                    second: "numeric",
                });

                const parts = formatter.formatToParts(date);
                const dateParts = {};

                parts.forEach((part) => {
                    if (part.type !== "literal") {
                        dateParts[part.type] = part.value;
                    }
                });

                const tzDay = parseInt(dateParts.day, 10);
                const tzMonth = parseInt(dateParts.month, 10);
                const tzYear = parseInt(dateParts.year, 10);
                const tzHour = parseInt(dateParts.hour, 10);
                const tzMinute = parseInt(dateParts.minute, 10);

                const formatters = {
                    d: tzDay,
                    DD: tzDay.toString().padStart(2, "0"),
                    M: tzMonth,
                    MM: tzMonth.toString().padStart(2, "0"),
                    MMM: new Date(tzYear, tzMonth - 1, 1).toLocaleString(
                        "en-US",
                        { month: "short" }
                    ),
                    MMMM: new Date(tzYear, tzMonth - 1, 1).toLocaleString(
                        "en-US",
                        { month: "long" }
                    ),
                    yy: tzYear.toString().slice(-2),
                    yyyy: tzYear,
                    H: tzHour,
                    HH: tzHour.toString().padStart(2, "0"),
                    h: tzHour % 12 || 12,
                    hh: (tzHour % 12 || 12).toString().padStart(2, "0"),
                    m: tzMinute,
                    mm: tzMinute.toString().padStart(2, "0"),
                    A: tzHour < 12 ? "AM" : "PM",
                };

                return format.replace(
                    /\b(?:d|DD|M|MM|MMM|MMMM|yy|yyyy|H|HH|h|hh|m|mm|A)\b/g,
                    (match) => formatters[match]
                );
            },
        };
    },
};
