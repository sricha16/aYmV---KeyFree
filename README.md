aYmV---KeyFree for UWyo COSC 4950/5
====================================

Group Name: aYmV

Project Name: KeyFree

Group Members:
	Matt Gern
	Taylor Legg
	Siena Richard

Project Description:
KeyFree is a secure and reliable way to store passwords or any sensitive information that is hard to remember but shouldn't be written down. It is a compact micro-controller with an audio adapter that  communicates via the headphone jack to store your passwords on a micro SD card. Informaton is accessed and stored through a web portal that utilizes JavaScript to encrypt the sensitive information. That way, the user only has to remember one key, rather than all of their passwords. This can also allow users to use more secure passwords, that are longer and composed of random characters, and not worry about forgetting them. Basically, it is one password to rule them all!

The web portal uses JavaScript so that all of the encryption can be done client side. This way, sensitive information is not being sent over the Internet, making it susceptible to attacks. As soon as the user hits submit, the sensitive information is encrypted to keep it secure. Currently, 128-AES CBC mode is being used to encrypt the sensitive information. After it has been encrypted, the data is sent to the device, where it reads it in and stores it on the micro SD card under a name that the user chooses. When the user wants to retrieve the information, they then only have to enter that one key they do have to remember, and the name they originally chose to save it under to get it back. The web portal then asks the device for that piece of data, and decrypts it before providing it to the user. 

There are several other features that come with KeyFree. One is that because all information is stored on the micro SD card, if for some reason the device fails or is damaged, the user can remove the micro SC card and insert it into a new device, and still have access to their sensitive information. Additionally, the device will be locked so that someone cannot just pick it up and start trying to obtain the sensitive information. The key used to unlock the device versus the key that will be used to encrypt the information can be the same or different, we will leave that up to the user. Other features may include a random password generator, the ability to wipe the micro SD card if too many attempts have been made to unlock it, multi-factor authentication, auto fill in the browser, sending user IP information if the password is wrong, only allowing use on certain approved devices, and rendering the micro SD card unusable if a threat is detected.

KeyFree can allow for more secure practices, in a convenient and reliable way. With major companies being breached more often, it is as important as ever to be aware of best security practices, and be as secure as possible. KeyFree is meant to help with this in any way possible.

Project File Descriptions:
//TO DO


// Original File Contents for Reference
// Done by Ruben Gamboa
This is nothing more than a directory structure to use for starting a new project.  
It contains the following folders:

* **Source:** This is where you should place all of your source code.  If your project
  uses multiple languages, you may find it useful to have subdirectories, e.g.,
  Source/java, Source/html, Source/images, etc.  If you are using an IDE that has
  a project directory, you may wish to start the project here.  For example, the
  Source directory could contain an Xcode project repository or a VisualStudio
  project.

* **Documents:** Here will be the documents you write to describe your project.  The
  boiler plate includes a use case template and a sample use case.  Both of these
  are taken from [Cockburn's use cases website][1], which contains many other useful tips
  on writing effective use cases (as does his book).  The use cases are written
  down in [Markdown][2] format.  You may convert them to your word processor of
  choice.  This folder also includes a sample specification document from a real
  software company.  That is a good example of what a requirements document for this
  class should look like.

* **Resources:** This is where you can place files that are useful to your project.
  For example, you may place here helpful articles downloaded from the internet.

Before you turn in a project, be sure to modify this file!  It should have:

* The name of your group, e.g., *Programming Nirvana*, but hopefully cooler

* The names of your group members (in the order, spelling, etc., that you wish to see 
  in the official notices of this class, e.g., in the invitations to the final presentations)

* The name of your project

* A brief description (paragraph or two) of your project

Of course, those elements can change, but warn me of any substantive changes after I
approve an earlier version.

This file should also give me a good description of the files and folders that you want 
me to look for grading purposes.  For example, if your code is in Java, you may describe 
the most important Java packages and the order in which I should view them to understand 
your code.  I will probably take a look at everything that is in the repository, but your
guidance will make it easier for me to navigate the project.
  
[1]: http://alistair.cockburn.us/Basic+use+case+template "Alistair Cockburn on Use Cases"
[2]: http://daringfireball.net/projects/markdown/ "Markdown Documentation"
