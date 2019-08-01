# API Reference

- [Call limits](#call-limits)
- [Logs](#logs)
- [PGP Secret and password](#pgp)
- [Responses](#responses)
    - [Success package](#success-package)
    - [Error package](#error-package)
- [Events](#events)
    - [Getting Starget](#getting-started)
    - [Consent Events](#consent-events)
        - [ConsentApproved](#consentapproved)
        - [ConsentDeclined](#consentdeclined)
        - [ConsentChanged](#consentchanged)
        - [ConsentNearExpiration](#consentnearexpiration)
        - [ConsentExpired](#consentexpired)
        - [ConsentRenewed](#consentrenewed)
        - [UserDataRequest](#userdatarequest)
        - [UserMessage](#usermessage)
    - [Data Events](#data-events)
        - [DataApproved](#dataapproved)
        - [DataDeclined](#datadeclined)
        - [DataChanged](#datachanged)
        - [DataNearExpiration](#datanearexpiration)
        - [DataExpired](#dataexpired)
        - [DataRenewed](#datarenewed)
        - [UserDataRequest](#userdatarequest-1)
        - [UserMessage](#usermessage-1)
    - [Breach Events](#breach-events)
        - [BreachPresented](#breachpresented)
        - [BreachBeeUsed](#breachbeeused)
        - [UserMessage](#usermessage-2)
- [System API](#system-api)
    - [ping](#ping)
    - [addEventSubscription](#add-event-subscription)
    - [listEventSubscriptions](#list-event-subscriptions)
    - [deleteEventSubscription](#delete-event-subscription)
    - [triggerEvent](#trigger-event)
    - [handleEvents](#handle-events)
    - [getEvents](#get-events)
- [Consent Administration API](#consent-administration-api)
    - [getCampaignInfo](#get-campaign-info)
    - [listCampaigns](#list-campaigns)
    - [getDataPackage](#get-data-package)
    - [listConsents](#list-consents)
    - [getConsentData](#get-consent-data)
    - [getCookieConsent](#get-cookie-consent)
    - [requestConsent](#request-consent)
    - [confirmConsentChange](#confirm-consent-change)
    - [declineConsentChange](#decline-consent-change)
    - [confirmDataDelete](#confirm-data-delete)
    - [setUserDataResponse](#set-user-data-response)
    - [registerConsents](#register-consents)
    - [getCampaignIdByRef](#get-campaign-id)
- [User API](#user-api)
    - [getUsePublicKey](#get-user-public-key)
    - [sendUserMessage](#send-user-message)
    - [getConversations](#get-conversations)
    - [getConversation](#get-conversation)
- [Bee API](#bee-api)
    - [listBees](#list-bees)
    - [uploadFiles](#upload-files)
    - [takeOff](#take-off)
- [Inbox API](#inbox-api)
    - [listInbox](#list-inbox)
    - [getInboxItem](#get-inbox-item)
    - [deleteInboxItem](#delete-inbox-item)
- [Troubleshooting](#troubleshooting)

# Call limits

If you are using developer accounts, please be aware that there is a call limit.
This is normally 120 calls per hour or 600 calls per 24 hour period.

For subscription accounts your call limits are determined per your account and
contract.  If you hit your call limits, you will need to contact your account
manager or support to increase them.

Once you have exceeded your call limits, your call will return status `429` too
many requests.


# Logs

API calls are logged like standard transactions with the same time to live
constraints.


# About PGP Secret and password {#pgp}

All PGP data is optional to the configuration object.  If you do not supply it,
then the SDK will skip decryption/encryption steps.  You will have to do these
outside the SDK and supply or process the data yourself.

If you wish to handle decryption of PGP yourself you can use the open source [openPGP javascript project](https://github.com/openpgpjs/openpgpjs) or any PGP library of your choosing. The raw event message is the encrypted payload for you to process.

# Responses

The XcooBee system has two modes of response. One, based on standard web HTTP REST API calls, and, two based on asynchronous webhooks (HTTP POST) when events occur.

The response package is structured into envelope and details. The envelope contains operational results such as `error` or `success` and time stamps. The detail contains associated return data for your call. XcooBee SDK will always return a value, even if it is an error object. In short, there is always a return. The absence of a return should be treated as error in standard responses.

Webhook (HTTP POST) responses are based on asynchronous practices. The XcooBee system will reach out and inform you about event. These are covered further down in this document.

## Success package

Object structure for response:

- time
- code (response code 200-299)
- request_id (the associated request reference)
- result (data object)
    - the result object is signed with your public PGP key if this is an **event** response.

## Error package

Object structure for error response:

- time
- code (response code: 300-500)
- request_id (the associated request reference)
- errors (array of error objects with data: message, detail)


# Events

Via the SDK, you can subscribe to many events that are available on the XcooBee network. As these occur at different times you will need to be able to accept communication from XcooBee via HTTP POST to a target site that is available via the Internet.

As an alternative to a public site, you can use the Event Polling mechanism as described later in this document to receive this communication.

## Getting Started

If there is a delayed response, the call will be accepted and the response returned via the HTTP POST event configuration. Your system should have a web-endpoint that is reachable by the XcooBee system for the event responses. A webhook is not a REST endpoint (HTTP - verb based).

The webhook system uses HTTPS `POST` calls to send information to you in response to event in XcooBee processing. You subscribe to events by calling the [`addEventSubscription()`](#add-event-subscription) method. More information and examples available in `examples/addEventSubscription.php` in this project.

Calling systems should be aware that there may be delays and that responses may be returned in different order from the call order.


### Webhook Delivery Signature
HTTP POST payloads that are delivered to your webhook's configured URL endpoint will contain several special headers:

Header | Description
--- | ---
XBEE-EVENT | Event type that triggered the delivery. E.g. ConsentApproved
XBEE-TRANS-ID  | A GUID identifying this event
XBEE-SIGNATURE | The HMAC hex digest of the response body*
XBEE-HANDLER | Name of a function that was configured as a handler for current event type

* The `XBEE-SIGNATURE` header will be sent if the webhook is configured with a secret. The HMAC hex digest is generated using the sha1 hash function and the secret as the HMAC key.


### Implementing Webhook Acceptance

You can implement your own webhook acceptor and handle all information as passed to you that way. Github has a good standard review of this process. [Github Webhook Examples](https://developer.github.com/webhooks/)


### Use SDK Tools for Event Handling

The SDK offers a methodology for accepting webhooks (HTTP POSTS) that:
- validates the content and signatures
- handles encryption / decryption
- manages the event handlers

This is a multi step process that is actually simple in the final implementation. It involves three parts:

a) determine how you wish to receive events

b) register your event subscription and handler pairs

c) process inbound events


#### `a` Determine how to receive events

You have two options on how to receive events from the XcooBee network. Event HTTP post (webhook) or Event polling.

##### HTTP POST
When elect to use HTTP POST, you need to supply a web accessible system or endpoint with which the XcooBee network can directly communicate. This occurs normally over port 443 (HTTPS/TLS). When an event occurs on the XcooBee network, for example, a user changes their consent agreement, the XcooBee network will trigger an event to this endpoint.

You specify your target URL during the setup of your consent campaign in the XcooBee UI. You cannot change or set it via the SDK.

##### Event Polling

For convenience we have built a Single Page Application (SPA) that can be used to bridge the event webhook system to your local application while it is in development.

If your application is under development and you need to work with events you can use the `poller` program. The poller program is a single page application that you can access from the <a href="https://github.com/XcooBee/example-event-poller-page">poller repo</a>. We recommend that you copy the contents to a local webserver. It acts as bridge to XcooBee events for you. This will allow relay events without  the need to publish your website.


#### `b` Register your Event Subscription and Handler Pairs


This is one of the simpler parts. As you subscribe to events using the [`addEventSubscription()`](#add-event-subscription) you also supply the PHP function that you wish to invoke when such an event is returned as function argument. See the function definition for more information.


#### `c` Process Inbound Events

The easiest way to process inbound event is to invoke the [`handleEvents()`](#handle-events) method of the `system` class.

Normally this is included in your site endpoint (page) that receives the HTTP POST from XcooBee. If you do event polling, you can pass the full POST body as `event` to the function.

This [`handleEvents()`](#handle-events) will validate call and call your handlers that you have declared in step `b`


### Standard Endpoint (target Url)

If you choose to accept events via webhook (HTTP POST), we recommend the that you create a standard endpoint to handle all communication. During the processing of this page you can, then, call the [`handleEvents()`](#handle-events) method.

The standard endpoint to create would be `/xbee/webhook`

If your site runs on `localhost` the fully formed webhook target URL would be `http://localhost/xbee/webhook`.


Depending on your framework and syntax library this may also be:

- `/xbee/webhook.php`
- `/xbee/webhook.jsp`
- `/xbee/webhook.cfm`
- `/xbee/webhook.aspx`

## Consent Events

These are events returned to your endpoint as part of user working with their consent center. All endpoints are determined inside each Consent Campaign.

### ConsentApproved
Fires when a consent request is approved. The consent object is returned.
It contains:
- consent reference
- data types
- consent type
- expiration date

### ConsentDeclined
Fires when a consent request is declined. You should remove user data and sent a XcooBee confirmation via [`confirmDataDelete()`](#confirm-data-delete).
The data submitted contains:
- consent reference

### ConsentChanged
Fires when consent is changed. A standard consent object is returned. You should confirm update and send XcooBee confirmation via [`confirmConsentChange()`](#confirm-consent-change).
It contains:
- consent reference
- data types
- consent type
- expiration date

### ConsentNearExpiration
Fires when an active consent is about to expire (inside 30 days). This is not exactly 30 days as the XcooBee system processes may push this slightly. You should plan ask for renewal of consent if you like to use the user data longer.
It contains:
- consent reference
- expiration date

### ConsentExpired
Fires when consent expired. You should remove user data and sent XcooBee confirmation via [`confirmDataDelete()`](#confirm-data-delete).
It contains:
- consent reference

### ConsentRenewed
Fires when an active consent was renewed by a user. You should confirm update and send XcooBee confirmation via [`confirmConsentChange()`](#confirm-consent-change).
It contains:
- consent reference
- expiration date

### UserDataRequest
Fires when user is making a request to extract their current data from your systems. This is to meet data-portability of GDPR. You should create data extract and send it to the User's XcooBee box. You can do so hiring the `xcoobee-data-response` bee with the GUID reference of the request.
It contains:
- consent reference
- xcoobeeId
- request reference

### UserMessage
Fires when user is sending you a message regarding a consent request.
Your campaign can enable/disable this feature in the `campaign options`. You can respond to this using a [`sendUserMessage()`](#send-user-message) call.
It contains:
- consent reference
- xcoobeeId
- message

## Data Events

Events submitted by XooBee to your system.

### DataApproved
Fires when consent is given and data has been supplied by user. A standard consent object is returned.
It contains:
- consent reference
- data types with data
- consent types
- expiration

### DataDeclined
Fires when user declined to provide data and consent. You should remove user data and sent XcooBee confirmation via [`confirmDataDelete()`](#confirm-data-delete).
It contains:
- consent reference

### DataChanged
Fires when data or consent is changed. A standard consent object is returned. You should confirm update and send XcooBee confirmation via [`confirmConsentChange()`](#confirm-consent-change).
It contains:
- consent reference
- data types with data
- consent types
- expiration

### DataNearExpiration
Fires when an active consent is about to expire (inside 30 days).
This is not exactly 30 days as the XcooBee system processes may push this slightly.
You should plan ask for renewal of consent if you like to use the user data longer.
It contains:
- consent reference
- expiration

### DataExpired
Fires when data has expired. You should remove user data and sent XcooBee confirmation via [`confirmDataDelete()`](#confirm-data-delete).
It contains:
- consent reference

### DataRenewed
Fires when an active consent was renewed by a user. You should confirm update and send XcooBee confirmation via [`confirmConsentChange()`](#confirm-consent-change).
It contains:
- consent reference
- expiration date

### UserDataRequest
Fires when user is making a request to extract their current data from your systems.
This is to meet data-portability of GDPR.
You should create data extract and send it to the User's XcooBee box.
You can do so hiring the `xcoobee-data-response` bee with the GUID reference of the request.
It contains:
- consent reference
- xcoobeeId

### UserMessage
Fires when user is sending you a message regarding a consent request.
Your campaign can enable/disable this feature in the `campaign options`. You can respond to this using the [`sendUserMessage()`](#send-user-message) function.
It contains:
- consent reference
- xcoobeeId
- message

## Breach Events

### BreachPresented
Fires when user has opened breach advice.
It contains:
- breach reference

### BreachBeeUsed
Fires when user has used a bee that you have identified in the breach advice.
It contains:
- breach reference
- bee reference

### UserMessage
Fires when user is sending you a message regarding a consent request.
Your campaign can enable/disable this feature in the `campaign options`. You can respond to this using the [`sendUserMessage()`](#send-user-message) function.
It contains:
- breach reference
- xcoobeeId
- message

# System API

**All system methods are available in** `$sdk->system`
For example: `$pingResult = $sdk->system->ping();`

## ping([config]) {#ping}

Can be called to see whether current configuration will connect to XcooBee system. This will return an error if your API user does not have a public PGP key on its profile.

options:

```
config => optional: the config object
```

### response
standard response object
- status 200 if success:
  - result will contain true
- status 400 if error


## addEventSubscription(arrayOfEventAndHandlerPairs[, campaignId, config]) {#add-event-subscription}

You can register subscriptions to hooks by calling the addEventSubscription function and providing the event (as specified in this document) and handler pairs `eventname: handler`.

There is no wildcard event subscription, however, you can add many handlers at one time.

Example:

```
$xcoobee->system->addEventSubscription(
    ['ConsentDeclined' => 'declinedHandler'],
    'ifddb4cd9-d6ea-4005-9c7a-aeb104bc30be',
    $config
);
```

This will subscribe you on the XcooBee system to receive `ConsentDeclined` events for the `ifddb4cd9-d6ea-4005-9c7a-aeb104bc30be` campaign and call your handler named `declinedHandler(event)` when such an event occurs.

All event data is attached to the `events` object in the function calls.

No response is expected directly from any of the event handlers so returns are void/null.

options:

```
events     => array with event and handler maps
campaignId => optional: the campaign Id to use if not default
config     => optional: the config object
```

### response

standard response object
- status 200 if success:
  - result will contain added events data
- status 400 if error


## listEventSubscriptions([campaignId, config]) {#list-event-subscriptions}

list current subscriptions.

options:

```
campaignId => optional: only get subscriptions for the campaign id
config     => optional: the config object
```
### response

standard response object
- status 200 if success:
  - result will contain subscription events data
- status 400 if error


## deleteEventSubscription(arrayOfEventNames[, campaignId, config]) {#delete-event-subscription}

delete existing subscriptions.
If you do not supply a campaign Id the event will for the default campaign Id will be deleted. If the subscription does not exists we will still return success.

options:

```
arrayOfEventNames => array with eventnames to be unsubscribed
campaignId        => optional: the campaign Id to use if not default
config            => optional: the config object
```
### response

standard response object
- status 200 if success:
  - result will contain deleted subscription events data
- status 400 if error


## triggerEvent(type[, config]) {#trigger-event}

Trigger test event to configured campaign webhook. The structure will be the same as real event (with encrypted payload and HMAC signature).
Also you will receive `XBEE-TEST-EVENT` header, which indicates that event is test. If campaign webhook is not configured, you'll receive an error.

options:

```
type   => name of event
config => optional: the config object
```
### response

standard response object
- status 200 if success:
  - result will contain test event with payload
- status 400 if error


## handleEvents([events])

This function does not require a call to the XcooBee API. It is rather the handler for calls that you recceive **from** XcooBee via webhooks as outlined previously.

You should embed this function into the route/endpoint processor that is invoked when XcooBee posts back to you one of the events that you have registered for earlier. You can find out which events these are by calling the [`listEventSubscriptions()`](#list-event-subscriptions) method.

The webhook post will be validated in this method and if post is valid, we will look into the registered events and further invoke the mapped placeholder function for each event that was created with the [`addEventSubscription()`](#add-event-subscription) method.

Optionally, the events data object may be passed along with the data from the HTTP header and POST call.

There is two modes of operation for `handleEvents()`.

a) Without function parameter `events`:

When you call `handleEvents()` without an `events` function argument, the method will look for the event data in the webhook (HTTP POST) stream. Verify the HTTP signatures against expected signatures and then process the event.

b) With function parameter `events`:

In case where you poll for events from XcooBee seperately you can call `handleEvents(events)` directly with the events. If you supply the events specifically, the method will not look for HTTP POST header variables for verification and instead will directly process events as provided.

options:

```
events => optional: array of objects with HTTP post data

```

### response
- no response, since this is an internal call to the mapped event handlers


## getEvents([config]) {#get-events}

In cases where you are not able to use direct webhook posts to your sites, for example you are in development or your system cannot be accessed via the Internet directly, you can pull your events from XcooBee.

If you are using pull based access make sure that you do not have an Endpoint defined in your campaign as XcooBee will otherwise attempt to post to the endpoint and register an error. Without a campaign endpoint, XcooBee will save events that you subscribe to and respond to your pull requests.

You have 72 hours to pick up the saved events. Once pulled events will be deleted. So please make sure that you save them if you need to replay them.

Your pull intervall should not be more than 1 `getEvents()` call per minute otherwise HTTP error 429 will be returned. We recommend every 62 seconds to avoid any timer issues.

options:

```
config => optional: the config object
```

### response

standard response object
- status 200 if success:
    - result will contain events with payload
- status 400 if error

# Consent Administration API

**All consent administration methods are available in** `$sdk->consents`
For example: `$campaigns = $sdk->consents->listCampaigns();`

## getCampaignInfo([campaignId, config]) {#get-campaign-info}
get basic info on campaign (setup, datatypes and options). The information will not return the users registered with the campaign.

options:

```
campaignId => optional: the campaign Id to use if not default
config     => optional: the config object
```

### response

standard response object
- status 200 if success:
    - result will contain campaign data object
- status 400 if error

## listCampaigns([config]) {#list-campaigns}
get all user campaigns

options:

```
config => optional: the config object
```

### response

standard response object
- status 200 if success:
    - result will contain array of campaign objects
- status 400 if error

## getDataPackage(consentId[, config]) {#get-data-package}

When data is hosted for you at XcooBee you can request the data package each time you need to use it. You will need to provide `consentId`. This call will only respond to authorized call source.

options:

```
consentId => the packagePointer for the data you wish to receive
config    => optional: the config object
```

### response

standard response object
- status 200 if success:
    - result will contain requested data object
        The SDK will decrypt this for you if it has access to PGP keys otherwise you have to decrypt this object
- status 400 if error

## listConsents([statuses, config]) {#list-consents}

Query for list of consents for a given campaign. Company can get general consentId for any consent that was created as part of a campaign. This is a multi-page recordset. Data returned: consentId, creation date, expiration date, xcoobeeId

possible response/filter for status:

pending, active, updating, offer, cancelled, expired, rejected

options:

```
statuses  => optional: array of numbers, one of the valid consent statuses, if not specified all will be returned
config    => optional: the config object
```

### response

standard response object
- status 200 if success:
    - result will contain consent data object: consent_id, status_id, date_c, date_e, xcoobee_id
- status 400 if error

## getConsentData(consentId[, config]) {#get-consent-data}

Query for a specific consent given. Company can get consent definition for any consent that was created. The data normally has three areas: Who, what data types, what the uses are, how long.

options:

```
consentId => the consent Id for which to retrieve information
config    => optional: the config object
```

### response

standard response object
- status 200 if success:
    - result will contain consent data object: user, datatypes, consenttypes, expiration
- status 400 if error

## getCookieConsent(xid[, campaignId, config]) {#get-cookie-consent}

This is a shortcut mechanism to query the XcooBee system for existing user consent for consent type `Website Tracking (1400), Web Application Tracking (1410)` for specific use data types (`application cookie (1600), usage cookie (1610), and advertising cookie (1620)`). We will retrieve only active consent for the cookies on the website identified in the campaign Id and return whether user has agreed to any cookies.

note:
- Your site in your campaign has to match the origin of the call since we do not use PGP encryption in this call for speed.
- The user has to be logged in to XcooBee

The return is a CSV list like this:
- application,usage,advertising

options:

```
xid        => XcooBee Id of the user to check for consent
campaignId => optional: the campaign id to use if not default
config     => optional: the config object
```

### response

standard response object
- status 200 if success:
    - result will contain website cookie consent CSV: application,usage,advertising
- status 400 if error

## requestConsent(xid[, refId, campaignId, config]) {#request-consent}

Sends out the consent or consent and data request to a specific user using the data in the campaign. The campaign definition determines what data (only consent or consent + data) we will ask from the user.

options:
```
xid        => XcooBee Id of the user to check for consent
refId      => optional: reference Id generated by you that identifies this request to you. Max 64 chars. This will returned to you in event response
campaignId => optional: the campaign id to use if not default
config     => optional: the config object
```

When user responds to the consent request a webhook will fire from XcooBee to the identified endpoint in the campaign. The SDK does not allow Endpoints to be created or changed. Please use the GUI.

### response

standard response object
- status 200 if success:
    - result will contain object with refId
- status 400 if error


## confirmConsentChange(consentId[, config]) {#confirm-consent-change}
Use this call to confirm that data has been changed in company systems according to change requested by user.

options:
```
consentId => the consent for which data is to be confirmed
config    => optional: the config object
```

### response

standard response object
- status 200 if success:
    - result will contain object with confirmed status
- status 400 if error


## declineConsentChange(consentId[, config]) {#decline-consent-change}
Use this call to open dispute on consent.

options:
```
consentId => the consent which should be disputed
config    => optional: the config object
```

### response

standard response object
- status 200 if success:
    - result will contain object with confirmed status
- status 400 if error

## confirmDataDelete(consentId[, config]) {#confirm-data-delete}
Send by company to confirm that data has been purged from company systems

options:
```
consentId => the consent for which data has been deleted
config    => optional: the config object
```

### response

standard response object
- status 200 if success:
    - result will contain object with confirmed status
- status 400 if error


## setUserDataResponse(message, requestRef, filename, targetUrl, eventHandler[, config]) {#set-user-data-response}

Companies can respond to user data requested via this call. Standard hiring points will be deducted for this. The call will send a `message` to user's communication center. You also need to send a file with user's data in order to close data request.

options:
```
message         => the text to be sent to the user as user data
requestRef      => unique identifier of the data request, you will receive this on `UserDataRequest` event
filename        => pointer to the file which contains user's data
targetUrl       => a webhook URL that will receive processing events
eventHandler    => name of a function that will process POST events sent to webhook URL
config          => optional: the config object
```

### response

standard response object
- status 200 if success:
    - result will contain progress and refId
- status 400 if error


## registerConsents([filename, targets, reference, campaignId, config]) {#register-consents}

Generally register/save consents that you received outside of XcooBee.

a) You may have already a list of consents that you have obtained from user but wish to use XcooBee as a system of record so you can manage them through XcooBee.
b) Or, you would like for XcooBee to verify the consent that you have in your corporate system. 

You can upload a list of emails for your campaign and, if your campaign is setup for it,  XcooBee will validate the consent for you with the users.

The list of users can be either provided in separate file or object array.
Must be provided at least one argument `filename` or `targets` otherwise will throws error.

options:
```
filename    => optional: pointer to the csv file which contains list of targets. 
targets     => optional: list of users whose consents we need to register

reference   => optional: user reference
campaignId  => optional: id of campaign to which consents must be assigned
config      => optional: the config object
```

### targets

targets is an array of objects:
```
target          => XiD or email address of a user whose consent we need to register
date_received   => date when a consent was received, optional, we'll use current date as default
date_expires    => date when a consent expires, optional, we'll set expiration date based on campaign's settings if missing
```

example:
```
[
   [ 'target' => 'example@test.com' ],
   [ 'target' => 'someTestXid', 'date_expires' => '2019-06-14T08:35:35.866Z' ],
]
```

### filename

filename is either an instance of File or a path to a file
it must be a csv format where each line represents target, file should not contain headers

| target           | date_received            | date_expires             |
|------------------|--------------------------|--------------------------|
| example@test.com | 2019-06-14T08:35:35.866Z | 2019-06-14T08:35:35.866Z |
| ~someTestXid     | 2019-06-14T08:35:35.866Z | 2019-06-14T08:35:35.866Z |

### response

You should save the refId that is returned from XcooBee. This is your reference to the consent for lookup purposes. 
XcooBee does not save the actual email of the user for the consent record, it uses a one way hash pattern. XcooBee technology is unable to resolve consent record via email until user has created an account.  Thus, you should save the refId returned. This is the only link between data stored in your system and XcooBee consent. 

When you submit multiple records via array object or file, the refId returned is only the **prefix** to the final reference for each consent. The final reference Id can be determined by the ordinal position (zero based index) of the record you submitted `refId-[ordinal position]`. Thus if you have a reference Id of `3657f2c0-d6a7-4a83-88db-0ad8ac4ca4e9` and you submitted two records, the final reference Id for each of the consent records would be:

- `3657f2c0-d6a7-4a83-88db-0ad8ac4ca4e9-0` for first record
- `3657f2c0-d6a7-4a83-88db-0ad8ac4ca4e9-1` for second record etc.



standard response object
- status 200 if success:
  - returns refId
- status 400 if error

## getCampaignIdByRef(campaignRef[, config]) {#get-campaign-id}
get campaign id by it's reference

options:

```
campaignRef => the campaign reference which is available on Consent Administration page
config      => optional: the config object
```

### response

standard response object
- status 200 if success:
  - returns campaignId or `null` if not found
- status 400 if error


# User API

**All users methods are available in** `$sdk->users`
For example: `$conversations = $sdk->users->getConversations();`

## getUserPublicKey(xid[, config]) {#get-user-public-key}

Retrieves a user's public PGP key as published on their public profile. If the user chose to hide it or the user is not known, it returns `null`.

example:
```
getUserPublicKey('~XcooBeeId');
```

options:
```
xid    => XcooBee Id of the user to get their public PGP key
config => optional: the config object
```

### response
public PGP or empty string

## sendUserMessage(message, reference, [config]) {#send-user-message}
This function allows you to send a message to users. You can communicate issues regarding consent, ticket and data request this way. It will create a threaded discussion for the user and for you and append to it this message.

options:
```
message   => the text to be sent to the user as user data, can be html formatted. Max 2000 characters
reference => object with type as key and identifier as value. e.g. ['consentId' => '...']. Currently supported identifiers:
    - consentId
    - ticketId
    - complaintRef
    - requestRef - data request reference (can be obtained in `UserDataRequest` event)
    Only one of identifiers should be provided
config    => optional: the config object
```

### response
standard response object
- status 200 if success:
    - data object will contain message object
- status 400 if error

## getConversations([config]) {#get-conversations}
This function allows you to get a list of discussions with users regarding breaches, consents and so on.

options:
```
config => optional: the config object
```

### response
standard response object
- status 200 if success:
    - result will contain list of conversations
- status 400 if error

## getConversation(userId[, config]) {#get-conversation}
This function allows you to get full discussion with selected user.

options:
```
userId => the user Id
config => optional: the config object
```

### response
standard response object
- status 200 if success:
    - result will contain list of messages with certain user
- status 400 if error



# Bee API

The Bee api is the principal interface to hire bees. Most of the times this will be accomplished in two steps. In the first step you upload your files to be processed by bees using [`uploadFiles()`](#upload-bees) call. If you did not specify an outbox endpoint you will also have to call the [`takeOff()`](#take-off) function with the processing parameters for the bee.

The immediate response will only cover issues with files for the first bee. If you want to be informed about the progress of the processing you will need to subscribe to events.

**All bees methods are available in** `$sdk->bees`
For example: `bees = $sdk->bees->listBees();`


## listBees([searchText, config]) {#list-bees}

This function will help you search through the bees in the system that your account is able to hire. This is a simple keyword search interface.

options:

```
searchtext => string of keywords to search for in the bee system name or label in the language of your account
config     => optional: the config object
```

### response
standard response object
- status 200 if success:
    - result will contain basic bee data: bee-systemname, bee-label, bee-cost, cost-type
- status 400 if error

## uploadFiles(files[, endpoint, config]) {#upload-files}

You use the uploadFiles function to upload files from your server to XcooBee. You can upload multiple files and you can optionally supply an outbox endpoint. If you have an outbox endpoint you do not need to call the [`takeOff()`](#take-off) function as the endpoint already specifies all processing parameters. If your subscription allows you can configure the outbox endpoints in the XcooBee UI.

options:
```
files    => array of strings with file pointers to the file store, e.g.: `c:\temp\mypic.jpg` or `/home/mypic.jpg`
endpoint => optional: the outbox endpoint, e.g. `marketing data` or `POS drop point`
config   => optional: the config object

```

### response
standard response object
- status 200 if success:
    - result will contain true
- status 400 if error

## takeOff(bees, options[, subscriptions, config]) {#take-off}

You normally use this as follow up call to [`uploadFiles()`](#upload-files). This will start your processing. You specify the bee(s) that you want to hire and the parameter that are needed for the bee to work on your file(s). If you want to be kept up to date you can supply subscriptions. Please note that subscriptions will deduct points from your balance and will cause errors when your balance is insufficient.

This is the most complex function call in the Bee API and has multiple options and parts.

a) parameters
b) subscriptions
c) subscription events

options:

```
bees          => array of bee system names (e.g. "xcoobee_digital_signature_detection") as key and their parameters as value
options       => general options
subscriptions => optional: the subscriptions array. Specifies the subscriptions
config        => optional: the config array
```

### `a` Parameters

Parameters can be bee specific or apply to the overall job.

specific bee parameters example:
```
$bees['xcoobee_testbee'] = [
    'height' => 599,
    'width' => 1200,
];
```

Overall job parameters to be used for the hiring are specified with the `process` prefix including destinations (recipients) to which you wish to send the output of the processing.

general process parameters example:
```
$options['process']['userReference'] = 'myownreference';
$options['process']['destinations'] = ['email@mysite.com', '~jonny'];
$options['process']['fileNames] = ['filename.png'];
```

general custom parameters example:
```
$options['custom'] = [
    'full_name' => 'John Doe',
    'age'       => 29
]
```

Bee parameters that are specified require the bee name prefix. If the bee name is `xcoobee_testbee` and it requires two parameters `height` and `width` then you will need to add these into an associative array inside the parameters array with a key of bee name.

### `b` Subscriptions

Subscriptions can be attached to the overall process. You will need to specify a `target`, an `events` and a `handler` argument at minimum. The `target` endpoint has to be reachable by the XcooBee system via **HTTP/S POST**. The `events` determines which events you are subscribing to.

Thus the three keys for each subscription are:
- target  => string with target endpoint URL
- events  => array with life-cycle events to subscribe to
- handler => required: the PHP function that will be called when we have bee API events

The HMAC signature will assume your XcooBee Id as the shared secret key and will use the the PGP public key to encrypt the payload. Without this you are still using SSL encryption for the transfer.

To subscribe to overall process events, the keyword `process` needs to be used. The subscription details need to be attached as subkeys to it.

Remember that subscriptions deduct points from your balance even if they are not successful so please validate that the endpoints you specify in `target` are valid.

Example of subscription on the overall process.

subscriptions example:
```
Process Subscriptions:
$subscriptions['target'] = "https://mysite.com/beehire/notification/"
$subscriptions['events'] = ["error", "success", "deliver", "present", "download", "delete", "reroute"]
$subscriptions['handler] = "myBeeEventHandler"

```

### `c` Subscription events

The event system for bees uses process level events.


#### Process Level events
- **error**
    - There was an error in the processing of the transaction. If there are multiple errors each of them is send in separate post.
    - Event type sent in POST header - `ProcessError`
- **success**
    - The overall transaction completed successfully
    - Event type sent in POST header - `ProcessSuccess`
- **deliver**
    - Is there was a transfer of a file, the file was delivered to the inbox of the recipient
    - Event type sent in POST header - `ProcessFileDelivered`
- **present**
    - If there was a transfer of a file, the file was seen by the user
    - Event type sent in POST header - `ProcessFilePresented`
- **download**
    - If there was a transfer of a file and the user downloaded the file
    - Event type sent in POST header - `ProcessFileDownloaded`
- **delete**
    - This event occurs when user deletes the file
    - Event type sent in POST header - `ProcessFileDeleted`
- **reroute**
    - When user has automated inbox rules and the document has been sent to a different place a reroute event is triggered
     - Event type sent in POST header - `ProcessReroute`


#### Event payload examples:
- success event example:
```
{
    "date": "2017-12-04T16:50:40.698Z",
    "file": "myImage.jpg",
    "userReference": "88jenr",
    "recipient": "~john873",
    "event": "success",
    "eventLevel": "bee",
    "source": "Block4-post",
    "beeName": "xcoobee_image_resizer",
    "transactionName": "9SDd8ccb"
}
```

- error event example:
```
{
    "date": "2017-10-24T15: 22: 39.209Z",
    "file": "myhome2.jpg",
    "userReference": "no-reference",
    "recipient": "no-recipient",
    "event": "error",
    "eventLevel": "bee",
    "source": "Block4-post",
    "beeName": "xcoobee_image_converter",
    "message": "Timeout error on container process",
    "transactionName": "0P4bbee"
}
```

### response
standard response object
- status 200 if success:
    - result will contain true
- status 400 if error


# Inbox API

The inbox API governs the access to your inbox. You can list, download, and delete items from your inbox.

**All inbox methods are available in** `$sdk->inbox`
For example: `$inboxItems = $sdk->inbox->listInbox();`

## listInbox([config]) {#list-inbox}

This method will present a paged list of inbox items that you can download. The listing will be for the user connected to you API key. You cannot check any other user's inbox using this method. You can return up to 100 items in one call.
Calling this method more than once per minute will result in HTTP 429 error (exceeding call limits).

You will the following data-points:
- messageId
- sender
- fileName
- fileSize (in bytes)
- receiptDate
- expirationDate
- downloadDate

Inbox items are listed in order of arrival with most recent items first.

options:
```
config => optional: the config object

```

### response
standard response object
- status 200 if success:
    - result will contain list of inbox items in array: messageId, sender, fileName, fileSize, receiptDate, expirationDate, downloadDate
- status 400 if error


## getInboxItem(messageId[, config]) {#get-inbox-item}

This method will return a file and file meta tags. Upon first downloaded, the `downloadDate` for the item will be populated.

options:
```
messageId => the message Id for the file to be downloaded
config    => optional: the config object
```

### response
standard response object
- status 200 if success:
    - result will contain the file and file_meta object (userRef, fileType, fileTags)
- status 400 if error


## deleteInboxItem(messageId[, config]) {#delete-inbox-item}

This method will delete a file that corresponds to your messageid. If the file does not exist, an error will be returned.

options:
```
messageId => the message Id for the file to be deleted
config    => optional: the config object
```

### response
standard response object
- status 200 if success:
    - result will contain true
- status 400 if error

# Troubleshooting

## Error 401
Certain SDK calls require authorized origins.

You may receive `401` error responses from XcooBee if your API call originates from an unauthorized domain or IP. Please make sure you registered your domain in your XcooBee campaign `CallBack URL` option.

## Error 429

Once you have exceeded your call limits, your call will return status `429` too many requests. Please update your subscription or contact support.

