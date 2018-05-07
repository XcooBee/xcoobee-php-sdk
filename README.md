# XcooBee PHP SDK

The XcooBee SDK is a facility to abstract lower level calls and implement standard behaviors.
The XcooBee team is providing this to improve the speed of implementation and show the best practices while interacting with XcooBee.


Generally, all communication with XcooBee is encrypted over the wire since none of the XcooBee systems will accept plain traffic. All data sent to XcooBee from you and vice versa is also signed using PGP protocols. The data packets that you will receive are signed with your public key and the packages that you send are signed with your private key.

If you need to generate new PGP keys you can login to your XcooBee account and go to the settings page to do so.

XcooBee systems operate globally but with regional connections. The SDK will be connecting you to your regional endpoint automatically. 

There is more detailed and extensive API documentation available on our [documentation site](https://www.xcoobee.com/docs).



## call limits

If you are using developer accounts please be aware that there is a call limit. This is normally 120 calls per hour or 600 calls per 24 hour period.

For subscription accounts your call limits are determined per your account and contract. If you hit your call limits, you will need to contact your account manager or support to increase them.

Once you have exceeded your call limits, your call will return status `429` too many requests.

## logs

API calls are logged like standard transactions with the same time to live constraints.


## Getting Started


### The config object

The config object carries all basic configuration information for your specific setup and use. It can be transparent handled by the SDK or specifically passed into every function. 
The basic information in the configuration is:

- your api-key
- your api-secret
- your pgp-secret
- your default campaign_id


The SDK will attempt to determine the configuration object based on the following schema:

1.) Use the configuration object passed into the function.

2.) Use the information as set by the setConfig call.

3.) Check the file system for the config file (.xcoobee/config)


### About PGP Secret and password

All PGP data is optional to the configuration object. If you do not supply it the SDK will skip decryption/encryption steps. You will have to do these outside the SDK and supply or process the data yourself.


#### setConfig(configModel)

The `setConfig` call is the mechanism to create the initial configuration object. You can use it multiple times. Each time you call you will override the existing data. The data once set will persist until library is discarded or `clearConfig` is called.

```
apiKey      => the api-key
apiSecret   => the api-secret
pgpSecret   => the pgp-secret key
pgpPassword => the pgp-password 
campaignId  => the default campaign_id
encode      => one of 0|1 where 0=no, 1=Yes, if 1 SDK will encrypt the contents of this file using machine specific mechanisms upon first use.
```

#### clearConfig()

Removes all configuration data in the configuration object.

#### config on file system

XcooBee SDK will search the file system for configuration info as last mechanism. You should ensure that the access to the config files is not public and properly secured. Once found the information is cached and no further lookup is made. If you change your configuration you will need to restart the process that is using the SDK to pick up the changes.

The XcooBee SDK will also encode and encrypt all values with a machine specific algorithm if you have set the flag (see below).
If you do not use the `encode` based mechanism we recommend that you also look into how to encrypt the contents for use only by the process that uses the XcooBee SDK.

The files will be located inside your `home` directory in the `.xcoobee` subdirectory. Thus the full path to config are:

`/home/.xcoobee/config` => the configuration options

`/home/.xcoobee/pgp.secret` => the pgp secret key in separate file


on Windows it is in the root of your user directory

`/Users/MyUserDir/.xcoobee/config` => the configuration option

`/Users/MyUserDir/.xcoobee/pgp.secret` => the pgp secret key in separate file

The initial content of the config file is plain text, with each option on a separate line.

**example file**:
```
apiKey=8sihfsd89f7
apiSecret=8937438hf
campaignId=ifddb4cd9-d6ea-4005-9c7a-aeb104bc30be
pgpPassword=somethingsecret
encode=0
```

options: 

```
apiKey         => the api-key
apiSecret      => the api-secret
campaignId     => the default campaign_id
pgpPassword    => the password for your pgp key
encode         => one of 0|1 where 0=no, 1=Yes, if 1 SDK will encrypt the contents of this file using machine specific mechanisms upon first use.
```



# Standard Responses

The XcooBee system has two modes of response. One, based on asynchronous webhooks, and, two, based on HTTP response basis. 

The response package is composed of envelope and details. The envelope contains operational results such as `error` or `success` and time stamps. The detail contains associated return data. In our call documentation we will not outline that there is an error returned, you should assume that there is. There is always a return. The absence of a return should be treated as error in standard responses.

Webhook responses are based on asynchronous practices and covered further down.

## Success package

Direct JSON responses

- time
- code (response code 200-299)
- data (data object) 
    - the data object is signed with your public PGP key

## Error package

- time
- code (response code: 300-500)
- error (the error object with data: message, detail)


# Webhook Events

If there is a delayed response, the call will be accepted and the response returned via the webhook event configuration. Your system should have a web-endpoint that is reachable by the XcooBee system for the event responses to reach you. A webhook is not a REST endpoint (HTTP - verb based).  

The webhook system uses HTTPS `POST` calls to send information to you in response to event in XcooBee processing. 

Calling systems should be aware that there may be delays and that responses may be returned in different order from the call order. 


## Delivery signature
HTTP POST payloads that are delivered to your webhook's configured URL endpoint will contain several special headers:


| Header | Description  | 
|----|---|
|X-XBEE-EVENT  |Event type that triggered the delivery. E.g. ConsentApproved | 
|X-TRANS-ID  |A GUID identifying this event.  | 
|X-XBEE-SIGNATURE |The HMAC hex digest of the response body*.  | 


* The `X-XBEE-SIGNATURE` header will be sent if the webhook is configured with a secret. The HMAC hex digest is generated using the sha1 hash function and the secret as the HMAC key.


## Implement yourself

You can implement your own webhook acceptor and handle all information as passed to you that way. Github has a good standard review of this process. [Github Webhook Examples](https://developer.github.com/webhooks/)



## Use SDK implementation

We offer prebuilt hooks and handling for you as part of the SDK. We will validate the communication and call the signature and call your assigned functions. See subscriptions functions later in this SDK document.

## Standard endpoint

The standard endpoint we create is `/xbee/webhook`

If your site runs on `localhost` the fully formed webhook would be `http://localhost/xbee/webhook`.


Depending on your framework and syntax library this may also be:

- `/xbee/webhook.php`
- `/xbee/webhook.jsp`
- `/xbee/webhook.cfm`



# System Calls

## ping([config])

Can be called to see whether current configuration will connect to XcooBee system. This will return an error if your API user does not have a public PGP key on its profile.

options: 

```
config   => optional: the config object
```

### response
standard JSON response object
- status 200 if success
- status 400 if error


## addEventSubscription(arrayOfEventAndHandlerPairs,[campaign_id],[config])

You can register subscriptions to hooks by calling the addEventSubscription function and providing the event and handler pairs `eventname => handler`.

There is no wildcard event subscription, however, you can add many handlers at one time.

```
Example JavaScript:
addEventSubscription([{"ConsentDeclined":"declinedHandler"}],"ifddb4cd9-d6ea-4005-9c7a-aeb104bc30be",myConfigObj);

```

This will subscribe you on the XcooBee system to receive `ConsentDeclined` events for the `ifddb4cd9-d6ea-4005-9c7a-aeb104bc30be` campaign and call your handler named `declinedHandler(event)` when such an event occurs.

All event data is attached to the `event` object in the function calls.

No response is expected directly from any of the event handlers so returns are void/null.

options: 

```
arrayOfEventAndHandlerPairs  => array object with event and handler maps
campaign_id                  => optional: the campaign id to use if not default
config                       => optional: the config object
```

### response

standard JSON response object
- status 200 if success
- status 400 if error




## listEventSubscriptions([campaign_id],[config])

list current subscriptions.

options: 

```
campaign_id => optional: Only get subscriptions for the campaign id
config      => optional: the config object
```
### response

standard JSON response object
- status 200 if success: 
    - data will contain current subscriptions dataset: type, campaign, last
- status 400 if error

## deleteEventSubscription(arrayOfEventNames, [campaign_id] ,[config])

delete existing subscriptions.
If you do not supply a campaign_id the event will for the default campaign id will be deleted. If the subscription does not exists we will still return success.


options: 

```
arrayOfEventNames  => array object with eventnames to be unsubscribed
campaign_id        => optional: the campaign id to use if not default
config             => optional: the config object
```
### response

standard JSON response object
- status 200 if success: 
    - data will contain the number of deleted subscriptions
- status 400 if error


# Consent Administration Calls For Consent

## getCampaignInfo([campaign_id], [config])
get basic info on campaign (setup, datatypes and options). The information will not return the users registered with the campaign.

options: 

```
campaign_id        => optional: the campaign id to use if not default
config             => optional: the config object
```

### response

standard JSON response object
- status 200 if success: 
    - data will contain campaign data object
- status 400 if error

## listCampaigns()
get all user campaigns

### response

standard JSON response object
- status 200 if success: 
    - data will contain array of campaign objects
- status 400 if error

## createCampaign(data)
Create campaign from passed data

options: 

```
data => object with data to create campaign. e.g. 

data.name ='test'
data.title[0].locale = 'en-us'
data.title[0].value = 'test'
data.description[0].locale = 'en-us'
data.description[0].value = 'test'
data.requests[0].name = 'test'
data.requests[0].request_data_types[0] = 'first_name'
data.requests[0].request_data_types[1] = 'last_name'
data.requests[0].request_data_types[2] = 'xcoobee_id'
data.requests[0].required_data_types[0] = 'first_name'
data.requests[0].required_data_types[1] = 'last_name'
data.requests[0].required_data_types[2] = 'xcoobee_id'
data.requests[0].consent_types[0] = 'deliver_a_product'
```

### response

standard JSON response object
- status 200 if success: 
    - data will contain campaign ref id
- status 400 if error

## modifyCampaign(campaignId, data)
Modify campaign with new data

options: 

```
campaignId  => the campaign id to use
data        => object with data to modify campaign. e.g. 

data.name ='test'
data.title[0].value = 'TEST'
```

### response

standard JSON response object
- status 200 if success: 
    - data will contain campaign ref id
- status 400 if error

## activateCampaign([campaignId])
Set status of campaign to active

options: 

```
campaign_id => optional: the campaign id to use if not default
```

### response

standard JSON response object
- status 200 if success: 
    - data will contain campaign ref id
- status 400 if error
     
## getDataPackage(packagePointer,[config])

When data is hosted for you at XcooBee you can request the data package each time you need to use it. You will need to provide `packagePointer`. This call will only respond to authorized call source.

options: 

```
packagePointer  => the packagePointer for the data you wish to receive
config          => optional: the config object
```

### response

standard JSON response object
- status 200 if success: 
    - data will contain requested data object
        The SDK will decrypt this for you if it has access to PGP keys otherwise you have to decrypt this object
- status 400 if error


## getConsentData(consentId,[config])

Query for a specific consent given. Company can get consent definition for any consent that was created. The data normally has three areas: Who, what data types, what the uses are, how long.

options: 

```
consent_id   => the consent id for which to retrieve information
config       => optional: the config object
```

### response

standard JSON response object
- status 200 if success: 
    - data object will contain consent data object: user, datatypes, consenttypes, expiration
- status 400 if error
   
## getCookieConsent(xid,[campaign_id],[config])

This is a shortcut mechanism to query the XcooBee system for existing user consent for consent type `Website Tracking (1400), Web Application Tracking (1410)` for specific use data types (`application cookie (1600), usage cookie (1610), and advertising cookie (1620)`). We will retrieve only active consent for the cookies on the website identified in the campaign id and return whether user has agreed to any cookies.

note: 
- Your site in your campaign has to match the origin of the call since we do not use PGP encryption in this call for speed.
- The user has to be logged in to XcooBee

The return is a CSV list like this:
- application,usage,advertising

options:

```
xid           => XcooBee ID of the user to check for consent         
campaign_id   => optional: the campaign id to use if not default.
config        => optional: the config object
```

### response

standard JSON response object
- status 200 if success: 
    - data object will contain website cookie consent CSV: application,usage,advertising
- status 400 if error

## requestConsent(xid,[refId],[campaign_id],[config])

Sends out the consent or consent and data request to a specific user using the data in the campaign. The campaign definition determines what data (only consent or consent + data) we will ask from the user.

options:
```
xid           => XcooBee Id of the user to check for consent 
refId         => optional: reference Id generated by you that identifies this request to you. Max 64 chars. This will returned to you in event response.
campaign_id   => optional: the campaign id to use if not default.
config        => optional: the config object
```

When user responds to the consent request a webhook will fire from XcooBee to the identified endpoint in the campaign. The SDK does not allow Endpoints to be created or changed. Please use the GUI.

### response

standard JSON response object
- status 200 if success: 
    - data object will contain true
- status 400 if error


## confirmConsentChange(consentId,[config])
Use this call to confirm that data has been changed in company systems according to change requested by user.

options:
```
consentid     => the consent for which data is to be confirmed
config        => optional: the config object
```

### response

standard JSON response object
- status 200 if success: 
    - data object will contain true
- status 400 if error

## confirmDataDelete(consentId,[config])
Send by company to confirm that data has been purged from company systems

options:
```
consentid     => the consent for which data has been deleted
config        => optional: the config object
```

### response

standard JSON response object
- status 200 if success: 
    - data object will contain true
- status 400 if error


## setUserDataResponse(message, consentId, [config])

Companies can respond to user data requested via this call (it is a shortcut way to hiring `xcoobee-data-response` bee). Standard hiring points will be deducted for this. The call will generate a pdf document based on the information in `message`. You should use the `Bee API` if you would like to send data files to users such as CSV, Excel, JSON etc.

options:
```
message          => the text to be sent to the user as user data, can be html formatted.
consentid     => the consent for which data has been deleted
config        => optional: the config object
```

### response

standard JSON response object
- status 200 if success: 
    - data object will contain true
- status 400 if error

## Consent Events (webhooks)

These are events returned to your endpoint as part of user working with their consent center. All endpoints are determined inside each Consent Campaign.

### ConsentApproved
Fires when a consent request is approved. 
The consent object is returned. It contains:
- consent reference
- data types
- consent types
- expiration

### ConsentDeclined
Fires when a consent request is declined. You should remove user data and sent a XcooBee confirmation via `confirmDataDelete()`.
The data submitted contains:
- consent reference

### ConsentChanged
Fires when consent is changed. A standard consent object is returned.
It contains:
- consent reference
- data types
- consent types
- expiration

### ConsentNearExpiration
Fires when an active consent is about to expire (inside 30 days).
This is not exactly 30 days as the XcooBee system processes may push this slightly.  
You should plan ask for renewal of consent if you like to use the user data longer.
It contains:
- consent reference
- expiration

### ConsentExpired
Fires when consent expired. You should remove user data and sent XcooBee confirmation via `confirmDataDelete()`.
It contains:
- consent reference

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
Your campaign can enable/disable this feature in the `campaign options`. You can respond to this using a `sendUserMessage()` call.
It contains:
- consent reference
- xcoobeeId
- message



# Consent Administration Calls For Data

This section covers events that are used in connection with gathering data and consent at the same time.

## Data Events (webhooks)

Events submitted by XooBee to your system.

### DataApproved
Fires when consent is given and data has been supplied by user. A standard consent object is returned.
It contains:
- consent reference
- data types with data
- consent types
- expiration

### DataDeclined
Fires when user declined to provide data and consent. You should remove user data and sent XcooBee confirmation via `confirmDataDelete()`.
It contains:
- consent reference

### DataChanged
Fires when data or consent is changed. A standard consent object is returned.
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
Fires when data has expired. You should remove user data and sent XcooBee confirmation via `confirmDataDelete()`.
It contains:
- consent reference

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
Your campaign can enable/disable this feature in the `campaign options`. You can respond to this using the `sendUserMessage()` function.
It contains:
- consent reference
- xcoobeeId
- message


# Message

## sendUserMessage(message, consentid, [breachid], [config])
This function allows you to send a message to users. You can communicate issues regarding breach, consent, and data this way. It will create a threaded discussion for the user and for you and append to it this message.

options:
```
message       => the text to be sent to the user as user data, can be html formatted. Max 2000 characters.
consentid     => the consent id that triggers the notification
breachid      => optional: related breach, user will receive a message with proposed actions declared in a breach
config        => optional: the config object
```

### response
standard JSON response object
- status 200 if success: 
    - data object will contain true
- status 400 if error

## getConversations([config])
This function allows you to get a list of discussions with users regarding breaches, consents and so on.

options:
```
config        => optional: the config object
```

### response
standard JSON response object
- status 200 if success: 
    - data object will contain true
- status 400 if error

## getConversation(userId, [config])
This function allows you to get full discussion with selected user.

options:
```
userId        => the user id 
config        => optional: the config object
```

### response
standard JSON response object
- status 200 if success: 
    - data object will contain true
- status 400 if error


# Breach

The breach API is the main way to interact with users during breach. The breach declaration and initial notifications occur in the UI.

## Breach Events (webhooks)

### BreachPresented
Fires when user has opened breach advice.
It contains:
- consent reference

### BreachBeeUsed
Fires when user has used a bee that you have identified in the breach advice.
It contains:
- consent reference
- bee reference

### UserMessage
Fires when user is sending you a message regarding a consent request. 
Your campaign can enable/disable this feature in the `campaign options`. You can respond to this using the `sendUserMessage()` function.
It contains:
- consent reference
- xcoobeeId
- message



# Bee API

The Bee api is the principal interface to hire bees. Most of the times this will be accomplished in two steps. In the first step you upload your files to be processed by bees using `uploadFiles()` call. If you did not specify an outbox endpoint you will also have to call the `takeOff()` function with the processing parameters for the bee.
The immediate response will only cover issues with files for the first bee. If you want to be informed about the progress of the processing you will need to subscribe to events.

## uploadFiles(files, [endpoint])

You use the uploadFiles function to upload files from your server to XcooBee. You can upload multiple files and you can optionally supply an outbox endpoint. If you have an outbox endpoint you do not need to call the `takeOff` function as the endpoint already specifies all processing parameters. If your subscription allows you can configure the outbox endpoints in the XcooBee UI.

options:
```
files      => array of strings with file pointers to the file store, e.g.: "c:\temp\mypic.jpg" or "home/mypic.jpg"
endpoint   => optional: the outbox endpoint, e.g. "marketing data" or "POS drop point"
```

### response
standard JSON response object
- status 200 if success: 
    - data object will contain true
- status 400 if error

## takeOff(bees, [options], [subscriptions])

You normally use this as follow up call to `uploadFiles()`. This will start your processing. You specify the bee(s) that you want to hire and the parameter that are needed for the bee to work on your file(s). If you want to be kept up to date you can supply subscriptions. Please note that subscriptions will deduct points from your balance and will cause errors when your balance is insufficient.

### Parameters Object

Parameters can be bee specific or apply to the overall job.

Overall job parameters to be used for the hiring are specified with the `process` prefix including destinations (recipients) to which you wish to send the output of the processing.

general process parameters example:
```
process.userReference="myownreference"
process.destinations=["~xcoobeeIds",{"xcoobee_id":"~jonny","accesskey":"isfnsfhis"},"emails"]
process.Integrations.XcooBeeInbox=[{"filename": "fileinInbox.png"}]
```

Bee parameters that are specified require the bee name prefix. If the bee name is `xcoobee_testbee` and it requires two parameters `height` and `width` then you will need to add these with prefix of bee-name to the parameters object. 

bee parameters example:
```
xcoobee_testbee.height = 599
xcoobee_testbee.width = 1200
```



### Subscriptions
Subscriptions can be attached to the overall process or for each bee. You will need to specify a `target` and an `events` argument at minimum. The `target` endpoint has to be reachable by the XcooBee system via **HTTP/S POST**. The `events` determines which events you are subscribing to.
Thus the three keys for each subscription are:
- target => string with target endpoint URL
- events => CSV string with life-cycle events to subscribe to
- signed => optional: default false, whether the content of the HTTPS POST is signed with your public PGP key


To subscribe to overall process events, the keyword `process` needs to be used instead of the bee system name. The subscription details need to be attached as subkeys to it. For bee level subscriptions, you will need to use the bee system name as prefix.
Remember that subscriptions deduct points from your balance even if they are not successful so please validate that the endpoints you specify in `target` are valid.

Example of subscription on the overall process and for one of the bees.

subscriptions example:
```
Process Subscriptions:
process.target = "https://mysite.com/beehire/notification/"
process.signed = true
process.events = "error,success,deliver,present,download,delete,reroute"

Bee subscriptions:
xcoobee_testbee.target = "https://somesite.com/testbee/notification/"
xcoobee_testbee.events = "success,error"
```


options:

```
bees          => array of bee system names, e.g. "xcoobee_digital_signature_detection"
parameters    => optional: the parameters object. For each bee by bee name.
subscriptions => optional: the subscriptions object. Specifies the subscriptions.
```


### response
standard JSON response object
- status 200 if success: 
    - data object will contain true
- status 400 if error

## listBees(searchText)

This function will help you search through the bees in the system that your account is able to hire. This is a simple keyword search interface.

options:

```
searchtext   => string of keywords to search for in the bee system name or label in the language of your account.
```

### response
standard JSON response object
- status 200 if success: 
    - data object will contain basic bee data: bee-systemname, bee-label, bee-cost, cost-type
- status 400 if error

## Bee Processing Events
The event system for bees can distinguish between process level and bee level events.

success event example:
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


error event example:
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


### Process Level
- error     => an error occured
- success   => everything completed
- deliver   => when destination: file was delivered to inbox
- present   => when destination: user saw the file
- download  => when destination: user downloaded file
- delete    => when destination: user deleted file
- reroute   => when destination: user triggered additional workflow

### Bee Level
- error     => an error occured
- success   => everything completed


# Troubleshooting

## Error 401
Certain SDK calls require authorized origins.

You may receive `401` error responses from XcooBee if your API call originates from an unauthorized domain or IP. Please make sure you registered your domain in your XcooBee campaign `CallBack URL` option. 


## Error 429

Once you have exceeded your call limits, your call will return status `429` too many requests. Please update your subscription or contact support.
