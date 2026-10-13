<?php

namespace App\Http\Controllers\Settings\EmailTemplate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\EmailTemplate\UpdateEmailTemplateRequest;
use App\Models\EmailTemplate;
use App\Services\EmailTemplate\EmailTemplateService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmailTemplateController extends Controller
{
    public function __construct(
        protected EmailTemplateService $service
    ) {}

    public function index()
    {
        $templates = $this->service->getAll();

        return Inertia::render('settings/EmailTemplates/Index', [
            'templates' => $templates,
        ]);
    }

    public function create()
    {
        return Inertia::render('settings/EmailTemplates/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:email_templates,slug',
            'subject' => 'required|string',
            'body' => 'required|string',
        ]);

        $this->service->store($data);

        return redirect()->route('email-templates.index')->with('success', 'Template created successfully.');
    }

    public function edit($id)
    {
        $template = EmailTemplate::findOrFail($id);

        return Inertia::render('settings/EmailTemplates/Edit', [
            'template' => $template,
        ]);
    }

    public function update(UpdateEmailTemplateRequest $request, int $id)
    {
        $data = $request->validated();

        $this->service->update($id, $data);

        return redirect()->route('email-templates.index')->with('success', 'Template updated successfully.');
    }

    public function destroy(int $id)
    {
        $this->service->delete($id);

        return redirect()->route('email-templates.index')->with('success', 'Template deleted.');
    }
}
