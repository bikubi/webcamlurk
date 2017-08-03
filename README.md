# webcamlurk

Capture static webcams sneakily.

* static = updating a JPEG periodically, handling display client side
* sneakily = minimize requests (in frequency, size and suspiciousness, by trying to converge towards update interval, checking headers only and setting referer and user-agent)

## Prerequisites

* PHP CLI with CURL
* Internet

## Usage

Takes three parameters: 

```
./webcamlurk.php PREFIX URL REFERER
```

1. a prefix for the downloaded image files
2. URL of the webcam. The string `$CNT` will be replaced with an increasing integer to simulate timestamp-like parameters.
3. a referer (might be necessary to trick some webcams / to mask your activities)

## Example usage

Captures a webcam in Ravenna, Italy:

```
./webcamlurk.php \
    example \
    'http://webcam1.comune.ra.it/record/current.jpg?rand=$CNT' \
    http://www.comune.ra.it/La-Citta/Webcam
```

## Example output

```
DISABLING HEADERS
Downloading from http://webcam1.comune.ra.it/record/current.jpg?rand=0 after 60 seconds
After 60 seconds, avgintv is now 55
70864 Bytes written to example2017-08-03_18:12:52
Sleeping for 65 seconds
Downloading from http://webcam1.comune.ra.it/record/current.jpg?rand=1 after 65 seconds
After 65 seconds, avgintv is now 55
70549 Bytes written to example2017-08-03_18:13:57
Sleeping for 65 seconds
Downloading from http://webcam1.comune.ra.it/record/current.jpg?rand=2 after 65 seconds
After 65 seconds, avgintv is now 55
70182 Bytes written to example2017-08-03_18:15:03
...
```

Different webcam which provides headers and has a lower frequency:

```
./webcamlurk.php bagnocostaazzurra http://www.bagnocostaazzurra.it/stazionemeteo/webcam.jpg http://www.bagnocostaazzurra.it/webcam/webcam.htm
PHP Warning:  PHP Startup: mailparse: Unable to initialize module
Module compiled with module API=20151012
PHP    compiled with module API=20160303
These options need to match
 in Unknown on line 0
Headers say last mod was 2017-08-03 16:28:32, will download.
Downloading from http://www.bagnocostaazzurra.it/stazionemeteo/webcam.jpg after 60 seconds
After 60 seconds, avgintv is now 55
15105 Bytes written to bagnocostaazzurra2017-08-03_18:28:54
Sleeping for 65 seconds
Headers say last mod was 2017-08-03 16:28:32, gonna skip.
Not downloading after 65 seconds
Sleeping for 30 seconds
Headers say last mod was 2017-08-03 16:28:32, gonna skip.
Not downloading after 95 seconds
Sleeping for 30 seconds
Headers say last mod was 2017-08-03 16:30:32, will download.
Downloading from http://www.bagnocostaazzurra.it/stazionemeteo/webcam.jpg after 125 seconds
After 125 seconds, avgintv is now 85
15162 Bytes written to bagnocostaazzurra2017-08-03_18:31:00
Sleeping for 95 seconds
...
```

## Why?

I ran this successfully over two weeks on fifteen webcams simultaneously on a Raspi A, for an art project.
Postprocessing / movie conversion done with my [image sequence blender](https://github.com/bikubi/image-sequence-blender).
