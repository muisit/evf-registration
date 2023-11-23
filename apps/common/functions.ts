import dayjs from 'dayjs';

export function is_valid(id:any)
{
    if (!id) return false;
    if (id.id) return is_valid(id.id);
    if (parseInt(id) > 0) return true;
    return false;
}

export function parse_date(dt:any = null) {
    var retval=dayjs(dt);
    if(!retval || !retval.isValid()) retval=dayjs();
    return retval;
}

export function valid_date(dt:any) {
    var retval=dayjs(dt);
    return retval.isValid();
}

export function format_date(date:any = null)
{
    var date2 = parse_date(date);
    return date2.format('YYYY-MM-DD');
}

export function format_datetime(date:any = null)
{
    var date2 = parse_date(date);
    return date2.format('YYYY-MM-DD HH:mm:ss');
}

var months=["January","February","March","April","May","June","July","August","September","October","November","December"];
export function format_date_fe(dt) {
    var mmt = dayjs(dt);
    return mmt.date() + " " + months[mmt.month()] + " " + mmt.year();
}
var short_months=["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
export function format_date_fe_short(dt) {
    var mmt = dayjs(dt);
    return mmt.date() + " " + short_months[mmt.month()];
}

export function random_token(length, charlist?:string) {
    if (!charlist) charlist = "abcdefghijklmnopqrstuvwxyz0123456789";
    if (length > 100) length = 100;
    if (length < 0) return null;
    var retval = '';
    for (var i=0;i < length; i++) {
        retval += random_from_list(charlist);
    }
    return retval;
}

export function random_from_list(lst) {
    if (!lst || !lst.length) return null;
    var index = random_int(lst.length);
    return lst[index];
}

export function random_int(max) {
    if (!max) max = 0x7fffffff;
    return Math.floor(Math.random() * max);
}

export function random_hash() {
    return dayjs().format("YYYYMMDDHHmmss");
}

export function date_to_category_num(dt:string|object, wrt:any = null) {
    var date=dayjs(dt);
    var date2=dayjs(wrt);
    var yearold=date.year();
    var yearnew = date2.year();
    var diff=yearnew - yearold;

    if(date2.month() > 7) {
        // add 1 if the event takes place in aug-dec, in which case we take birthyears as-of-next-january
        diff += 1;
    }
    var catnum =  Math.floor(diff / 10) - 3;
    console.log('year difference is ', dt, wrt, diff, ' catnum', catnum);

    // category 5 was removed, the highest category is now 4
    if(catnum > 4) catnum = 4;
    if(catnum < 1) catnum = 0;
    return catnum;
}

export function my_category_is_older(mycat:number, theircat:number) {
    if(mycat <= 0) return false; // no category for wrong birthdays
    return mycat > theircat;
}

export function date_to_category(dt:string|object, wrt:string|object|null|undefined) {
    var cat = date_to_category_num(dt, wrt);
    switch(cat) {
    // category 5 was removed from the implementation after the congress 2021
    case 5:// return "Cat 5";
    case 4: return "4";
    case 3: return "3";
    case 2: return "2";
    case 1: return "1";
    default: return "None";
    }
}
