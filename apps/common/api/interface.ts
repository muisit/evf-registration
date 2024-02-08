import { useAuthStore } from '../stores/auth';

let controller:AbortController|null = null;

export interface FetchResponse {
    data: any;
    status: number;
};

export function abortAllCalls() {
    if(controller) {
        controller.abort();
        controller = null;
    }
}

function glueParameters(path:string, data:any)
{
    var glue='?';
    if (path.indexOf('?') > 0) {
        glue = '&';
    }
    var elements = Object.keys(data).map((key) => {
        return key + '=' + data[key];
    });
    return path + glue + elements.join('&');
}

function simpleFetch(method: string, path:string, data:any, options:object|null = {}, headers={}, postprocessor:any = null) {
    if(!controller) {
        controller = new AbortController();
    }

    const auth = useAuthStore();
    const contentHeaders = Object.assign({
            "Accept": "application/json",
            "Content-Type": "application/json"
        },
        headers,
        {
            'X-CSRF-Token': auth ? auth.token : ''
        }
    );

    const fetchOptions = Object.assign({}, {headers: contentHeaders}, options, {
        credentials: "include",
        redirect: <RequestRedirect>"manual",
        method: method,
        signal: controller.signal
    }) as RequestInit;

    if (!data.event) data.event = auth.eventId;
    if (!data.country) data.country = auth.countryId;

    if (data && (method == 'POST' || method == 'PUT' || method == 'DELETE')) {
        fetchOptions.body = JSON.stringify(data);
    }
    if (data && method == 'GET') {
        path = glueParameters(path, data);
    }

    return fetch(import.meta.env.VITE_API_URL + path, fetchOptions)
        .then(postprocessor())
        .catch(err => {
            console.log(err);
            if (err.name == 'csrf') {
                console.log("received csrf error, getting new token and retrying");
                return auth.sendMe().then(() => {
                    console.log("retrying original call");
                    return simpleFetch(method, path, data, options, headers, postprocessor);
                });
            }
            else if(err.name == "AbortError") {
                console.log('skipping AbortError in Fetch call');
            }
            else {
                console.log("error in fetch: ", err);
                //throw err;
            }
        });
}

function validateResponse() {
    return async (res:any) => {
        var dt = {
            data: await res.json(),
            status: res.status
        };
        if (dt.status == 400) {
            let err = new Error("X-CSRF Error");
            err.name = 'csrf';
            throw err;
        }
        return dt;
    };
}

export function fetchJson(method:string, path:string, data={}, options = {}, headers = {}): Promise<FetchResponse> {
    return (simpleFetch(method, path, data, options, headers, validateResponse) as unknown) as Promise<FetchResponse>;
}

function attachmentResponse() {
    return (res:any) => {
        if (res.status == 200) {
            return res.blob().then((blob:any)=> {
                var file = window.URL.createObjectURL(blob);
                window.location.assign(file);
                window.URL.revokeObjectURL(file); // immediately release again
            });
        }
        else {
            alert("Failed to download attachment");
        }
    };
}

export function fetchAttachment(path:string, data:any = {}) {
    const auth = useAuthStore();
    if (!data.event) data.event = auth.eventId;
    if (!data.country) data.country = auth.countryId;
    path = glueParameters(path, data);
    window.location.href = import.meta.env.VITE_API_URL + path;
    //return simpleFetch('GET', path, data, options, headers, attachmentResponse);
}

export function uploadFile(path:string, selectedFile:any, add_data:any, options={}, headers={}): Promise<FetchResponse> {
    return (doUploadFile(path, selectedFile, add_data, options, headers) as unknown) as Promise<FetchResponse>;
}

function doUploadFile(path:string, selectedFile:any, add_data:any, options={}, headers={}) {
    if(!controller) {
        controller = new AbortController();
    }

    const auth = useAuthStore();
    const contentHeaders = Object.assign({
            "Accept": "application/json",
        },
        headers,
        {
            'X-CSRF-Token': auth ? auth.token : ''
        }
    );

    var data = new FormData()
    data.append('picture', selectedFile);

    if (!add_data.event) add_data.event = auth.eventId;
    if (!add_data.country) add_data.country = auth.countryId;
    Object.keys(add_data).map((key)=> {
        data.append(key, add_data[key]);
    });

    const fetchOptions = Object.assign({}, {headers: contentHeaders}, options, {
        credentials: <RequestCredentials>"include",
        redirect: <RequestRedirect>"manual",
        method: 'POST',
        signal: controller.signal,
        body: data
    });

    return fetch(import.meta.env.VITE_API_URL + path, fetchOptions)
        .then(validateResponse())
        .catch(err => {
            if(err.name !== "AbortError") {
                console.log("error in fetch: ",err);
                throw err;
            }
        });
}
