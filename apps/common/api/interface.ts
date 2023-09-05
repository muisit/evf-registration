import { useAuthStore } from '../stores/auth';

let controller:AbortController|null = null;

export function abortAllCalls() {
    if(controller) {
        controller.abort();
        controller = null;
    }
}

function simpleFetch(method: string, path:string, data:object|null|undefined, options:object|null = {}, headers={}, postprocessor:any = null) {
    if(!controller) {
        controller = new AbortController();
    }

    const auth = useAuthStore();
    const contentHeaders = Object.assign({
        "Accept": "application/json",
        "Content-Type": "application/json"},
        headers,
        {
            'X-CSRF-Token': auth ? auth.token : ''
        }
    );

    const fetchOptions = Object.assign({}, {headers: contentHeaders}, options, {
        credentials: "include",
        redirect: "manual",
        method: method,
        signal: controller.signal
    });

    if (data && (method == 'POST' || method == 'PUT' || method == 'DELETE')) {
        fetchOptions.body = JSON.stringify(data);
    }

    return fetch(import.meta.env.VITE_API_URL + path, fetchOptions as RequestInit)
        .then(postprocessor())
        .catch(err => {
            if(err.name != "AbortError") {
                console.log("error in fetch: ",err);
                throw err;
            }
        });

}

function validateResponse() {
    return res => res.json();
}

export function fetchJson(method:string, path:string, data={}, options = {}, headers = {}) {
    return simpleFetch(method, path, data, options, headers, validateResponse);
}

function attachmentResponse() {
    return res => {
        return res.blob().then((blob)=> {
            var file = window.URL.createObjectURL(blob);
            window.location.assign(file);
        });
    };
}

export function fetchAttachment(path:string, data={}, options={}, headers={}) {
    return simpleFetch('GET', path, data, options, headers, attachmentResponse);
}

export function uploadFile(selectedFile, add_data, options={}, headers={}) {
    if(!controller) {
        controller = new AbortController();
    }

    const contentHeaders = Object.assign({
        "Accept": "application/json",
        },
        headers
    );

    var data = new FormData()
    data.append('picture', selectedFile);
    Object.keys(add_data).map((key)=> {
        data.append(key, add_data[key]);
    })

    const fetchOptions = Object.assign({}, {headers: contentHeaders}, options, {
        credentials: "same-origin",
        redirect: "manual",
        method: 'POST',
        signal: controller.signal,
        body: data
    });

    return fetch(import.meta.env.VITE_API_URL + "/upload", fetchOptions)
        .then(validateResponse())
        .catch(err => {
            if(err.name !== "AbortError") {
                console.log("error in fetch: ",err);
                throw err;
            }
        });
}
