@component('mail::message')

# ⚠️ Document Due Soon

Hello {{ $notifiable->name ?? 'Team Member' }},

Your document needs attention before its deadline. Please review the routing details below and take action to keep it on track.

@component('mail::panel')
**Title:** {{ $document->title }}  
**SLA:** {{ $document->sla ?? 'Standard' }}  
**Priority:** {{ $document->priority ?? 'Normal' }}  
**Status:** {{ $document->status }}  
**Due Date:** {{ optional($document->due_date)->format('F j, Y') ?? 'TBD' }}
@endcomponent

@component('mail::button', ['url' => $url, 'color' => 'warning'])
View Document
@endcomponent

If you need help, reply to this message or check the document in the dashboard.

Thanks,<br>
**NAAP Routing Team**

@endcomponent
