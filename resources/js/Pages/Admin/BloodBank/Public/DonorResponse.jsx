import { Head } from '@inertiajs/react';

// Shown to a donor after tapping accept / decline in the email. No login, no app chrome.
export default function DonorResponse({ recorded, response, hospital, donor }) {
    let heading;
    let body;

    if (!recorded) {
        heading = 'This request is closed';
        body = 'It has been fulfilled, cancelled or has run out of time, so your reply was not recorded. Thank you for being ready to help.';
    } else if (response === 'accepted') {
        heading = `Thank you, ${donor}`;
        body = `${hospital} has been told you can donate. Please go there as soon as you can and ask for the blood bank.`;
    } else {
        heading = 'Reply recorded';
        body = `${hospital} knows you cannot donate this time. Thank you for letting them know.`;
    }

    return (
        <div className="flex min-h-screen items-center justify-center bg-slate-50 px-4">
            <Head title="Blood request" />
            <main className="w-full max-w-md rounded-lg bg-white p-8 shadow-sm ring-1 ring-slate-200">
                <h1 className="text-xl font-semibold text-slate-900">{heading}</h1>
                <p className="mt-3 text-sm leading-6 text-slate-600">{body}</p>
            </main>
        </div>
    );
}
