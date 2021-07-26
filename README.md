# Upldr

Upldr is a simple, easy to use php library for handling file uploads and storing them on client side.

# How to use?

```php
<?php
define("UPLDR", true); // To make sure that file won't get accessed directly
include 'upldr.php';

$user_id = "some_unique_user_ID"
$user_store = new Upldr($user_id, true /* Set to false to disallow any kind of write calls. */); 
$user_store->pwd() // "/"
$user_store->mkdir("Things"); // Create new directory.
$user_store->cd("Things"); // Change directory.
$user_store->pwd(); // "/Things" 
if (isset($_FILES["fileToUpload"])) {
    $user_store->createFileUploaded($_FILES["fileToUpload"], basename($_FILES["fileToUpload"]["name"]));
    // An example form:
    //<form method="post" enctype="multipart/form-data">
    //    <input type="file" name="fileToUpload" id="fileToUpload">
    //    <input type="submit" value="Upload" name="submit">
    //</form>
}
$user_store->cd("/") // Change back to root, one could also use '..'
$user_store->createFileGet("https://cataas.com/cat", "cat.png"); // use file_get_contents to write to 'cat.png'
$user_store->copy("cat.png", "Things/better_cat.png");
$user_store->ls();
```