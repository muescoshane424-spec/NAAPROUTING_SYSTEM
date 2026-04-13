# API Reference - NAAP Routing System Enhancements

## Endpoints Overview

### Document Management

#### Upload Document
```
POST /documents
Parameters:
  - title (required)
  - description (optional)
  - priority (required): Low, Medium, High
  - sla (required): Standard, Expedited, Critical
  - origin_office_id (required)
  - destination_office_id (required)
  - receiver_user_id (required)
  - file (required)

Response:
  - Document created with auto-generated QR code
  - Receiver automatically notified
  - Activity logged
```

#### Get Document Details
```
GET /track/{id}
Response:
  - Document info
  - Routing history
  - Signature proof (if signed)
  - All timestamps
```

### Routing & QR

#### Route Document
```
POST /routing/update/{id}
Parameters:
  - office_id (required)
  - receiver_user_id (optional)
  - notes (optional)

Response:
  - Document location updated
  - Activity logged
  - Receiver notified
  - Real-time tracking updated
```

#### Scan QR Code
```
POST /scan-qr/process
Parameters:
  - qr_data (required): Document ID or URL
  - signature (optional): Base64 signature

Response (JSON):
  {
    "success": true,
    "message": "...",
    "document": {
      "id": 123,
      "title": "...",
      "status": "...",
      "receiver": "...",
      "current_office": "...",
      "has_signature": true,
      "scanned_at": "Apr 09, 2026 14:30"
    }
  }
```

---

## Model Methods

### Document Model

#### Notification Methods
```php
// Send notification to receiver
$document->notifyReceiver();

// Send notification to uploader when signed
$document->notifyUploader($signerName);

// Mark as received with signature
$document->markAsReceived($signatureData);

// Check if delivered and signed
$document->isDelivered(); // returns boolean

// Get delivery proof
$document->delivery_proof; // returns array or null
```

#### Status Properties
```php
$document->status; // "Pending", "In Transit", "Completed"
$document->receiver_signature; // Base64 image
$document->qr_scanned_at; // DateTime when QR scanned
$document->received_at; // DateTime when document received
$document->routing_notes; // routing information
```

### DocumentRouting Model

```php
// Fields
$routing->status; // "In Transit", "Completed"
$routing->notes; // routing notes
$routing->signed_by; // user who signed
$routing->signature; // Base64 signature
$routing->received_at; // DateTime received

// Relations
$routing->document();
$routing->fromOffice();
$routing->toOffice();
```

### Activity Log

```php
// Logged Actions
ActivityLog::create([
    'user' => session('user_name'),
    'action' => 'Document Created', // or 'Document Routed', 'QR Scanned', 'QR Scanned - Signed'
    'document_id' => $doc->id,
    'ip' => $request->ip(),
    'meta' => json_encode([
        'timestamp' => now()->toIso8601String(),
        // other metadata
    ])
]);
```

---

## Database Schema

### Documents Table (New Fields)
```sql
ALTER TABLE documents ADD COLUMN receiver_signature LONGTEXT NULL;
ALTER TABLE documents ADD COLUMN qr_scanned_at TIMESTAMP NULL;
ALTER TABLE documents ADD COLUMN received_at TIMESTAMP NULL;
ALTER TABLE documents ADD COLUMN routing_notes TEXT NULL;
```

### Document Routings Table (New Fields)
```sql
ALTER TABLE document_routings ADD COLUMN notes TEXT NULL;
ALTER TABLE document_routings ADD COLUMN signed_by VARCHAR(255) NULL;
ALTER TABLE document_routings ADD COLUMN signature LONGTEXT NULL;
ALTER TABLE document_routings ADD COLUMN received_at TIMESTAMP NULL;
```

### Notifications Table
```sql
CREATE TABLE notifications (
    id UUID PRIMARY KEY,
    type VARCHAR(255),
    notifiable_type VARCHAR(255),
    notifiable_id BIGINT,
    data JSON,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## Notification Classes

### DocumentRoutedNotification

```php
// Triggered when document is routed
$receiver->notify(new DocumentRoutedNotification($document));

// Data:
[
    'document_id' => 123,
    'document_title' => 'Certificate',
    'message' => 'Document "Certificate" has been routed to you',
    'type' => 'document_routed'
]

// Email: Sent with link to view document
```

### DocumentSignedNotification

```php
// Triggered when document is signed/received
$uploader->notify(new DocumentSignedNotification($document, $signerName));

// Data:
[
    'document_id' => 123,
    'document_title' => 'Certificate',
    'message' => 'Document "Certificate" has been signed and received',
    'type' => 'document_signed',
    'signer' => 'John Doe'
]

// Email: Includes signature details
```

---

## QR Code System

### QR Generation
```php
// Automatic on document creation
$qrCode = new QrCode(route('documents.show', $document->id), size: 300);
$writer = new PngWriter();
$result = $writer->write($qrCode);
$qrCodeData = $result->getString();
Storage::disk('public')->put($qrPath, $qrCodeData);
```

### QR Data
- **Contains**: Full URL to document
- **Format**: PNG image
- **Location**: `storage/app/public/qr_codes/{id}.png`
- **URL**: `/storage/qr_codes/{id}.png`
- **Size**: 300x300 pixels

---

## Activity Logging

### Events Logged

```
Document Created
- User
- Document ID
- IP Address
- Filename
- Timestamp

Document Routed  
- User
- Document ID
- From Office
- To Office
- Receiver (if assigned)
- Status change
- Timestamp
- Routing ID

QR Scanned
- User
- Document ID
- Receiver name
- Timestamp

QR Scanned - Signed
- User
- Document ID
- Receiver name
- Proof of delivery flag
- Timestamp
```

### Query Activity Logs
```php
ActivityLog::where('document_id', $id)
    ->orderBy('created_at', 'desc')
    ->get();

// With search
ActivityLog::whereHas('document', function($q) {
    $q->where('title', 'like', "%search%");
})->get();
```

---

## Timestamp Formats

All timestamps use these formats:

**In API Responses:**
```
ISO 8601: 2026-04-09T14:30:45+00:00
```

**In UI Display:**
```
M j, Y H:i:s
Example: Apr 9, 2026 14:30:45
```

**In Database:**
```
TIMESTAMP: 2026-04-09 14:30:45
```

---

## Response Codes

### Success
```
200 OK - Operation successful
201 Created - New resource created
202 Accepted - Notification sent
```

### Client Errors
```
400 Bad Request - Invalid parameters
422 Unprocessable Entity - Validation failed
404 Not Found - Document not found
403 Forbidden - Unauthorized access
```

### Server Errors
```
500 Internal Server Error - Server error
503 Service Unavailable - Service down
```

---

## Error Handling

### Example Response (Error)
```json
{
    "success": false,
    "message": "Invalid QR Code. Document not found.",
    "error": true
}
```

### Example Response (Success)
```json
{
    "success": true,
    "message": "Document received and signed successfully!",
    "document": {
        "id": 123,
        "title": "Certificate",
        "status": "Completed",
        "receiver": "John Doe",
        "current_office": "HR Office",
        "has_signature": true,
        "scanned_at": "Apr 9, 2026 14:30"
    }
}
```

---

## Configuration

### .env Settings
```
# Mail Configuration (for notifications)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=xxx
MAIL_PASSWORD=xxx
MAIL_FROM_ADDRESS=noreply@naap.org
MAIL_FROM_NAME="NAAP Routing"

# Storage
APP_URL=http://localhost:8000
```

### Queue Configuration (Optional)
For async notifications:
```
QUEUE_CONNECTION=database

# Or use Redis for better performance
QUEUE_CONNECTION=redis
```

---

## Testing Examples

### Test QR Scan with Signature
```php
$response = $this->post('/scan-qr/process', [
    'qr_data' => $document->id,
    'signature' => base64_encode($signatureImageData)
]);

$this->assertTrue($response->json('success'));
$this->assertNotNull($document->receiver_signature);
$this->assertEquals('Completed', $document->status);
```

### Test Routing
```php
$response = $this->post("/routing/update/{$id}", [
    'office_id' => $office->id,
    'receiver_user_id' => $user->id,
    'notes' => 'Urgent routing'
]);

$this->assertTrue($response->json('success'));
$this->assertEquals($office->id, $document->current_office_id);
```

---

## Performance Considerations

1. **Notifications**: Queue them for better performance
2. **QR Codes**: Generated once, cached on disk
3. **Tracking**: Queries include eager loading
4. **Activity Logs**: Paginated for large datasets
5. **Signatures**: Stored as Base64, consider CDN for images

---

## Security Best Practices

1. ✅ All actions logged with IP
2. ✅ Signatures verify document receipt
3. ✅ Timestamps prevent tampering
4. ✅ Activity audit trail available
5. ✅ Access control per document
6. ✅ Notifications confirm operations

---

**API is production-ready!**
