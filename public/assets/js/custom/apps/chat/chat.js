"use strict";

// Define the KTAppChat module
var KTAppChat = function () {
    // Function to send and display messages in the chat
    var sendMessage = function (chatContainer) {
        // Get the chat elements: messages container and input field
        var messageContainer = chatContainer.querySelector('[data-kt-element="messages"]');
        var inputField = chatContainer.querySelector('[data-kt-element="input"]');
return;
        // Check if the input field has text
        if (inputField.value.length !== 0) {
            // Get the message templates for outgoing and incoming messages
            var outgoingTemplate = messageContainer.querySelector('[data-kt-element="template-out"]');
            var incomingTemplate = messageContainer.querySelector('[data-kt-element="template-in"]');
            console.log(outgoingMessage)
            // Clone the outgoing message template and set the message text
            var outgoingMessage = outgoingTemplate.cloneNode(true);
            outgoingMessage.classList.remove("d-none");
            outgoingMessage.querySelector('[data-kt-element="message-text"]').innerText = inputField.value;

            // Clear the input field after sending the message
            inputField.value = "";

            // Append the outgoing message to the chat container and scroll to the bottom
            messageContainer.appendChild(outgoingMessage);
            messageContainer.scrollTop = messageContainer.scrollHeight;

            // After 2 seconds, show a thank-you message (simulating a response)
            setTimeout(function () {
                // Clone the incoming message template and set the response text
                var incomingMessage = incomingTemplate.cloneNode(true);
                incomingMessage.classList.remove("d-none");
                // incomingMessage.querySelector('[data-kt-element="message-text"]').innerText = "Thank you for your awesome support!";

                // Append the incoming message to the chat container and scroll to the bottom
                messageContainer.appendChild(incomingMessage);
                messageContainer.scrollTop = messageContainer.scrollHeight;
            }, 2000); // Simulate a 2-second delay for the incoming message
        }
    };

    return {
        // Initialize the chat functionality
        init: function (chatContainer) {
            if (chatContainer) {
                // Listen for Enter key press in the input field to send a message
                KTUtil.on(chatContainer, '[data-kt-element="input"]', "keydown", function (event) {
                    if (event.keyCode === 13) {  // Enter key
                        sendMessage(chatContainer);  // Send the message
                        event.preventDefault();  // Prevent the default Enter key behavior (e.g., form submission)
                        return false;  // Prevent further event propagation
                    }
                });

                // Listen for the Send button click to send the message
                KTUtil.on(chatContainer, '[data-kt-element="send"]', "click", function () {
                    sendMessage(chatContainer);  // Send the message when the button is clicked
                });
            }
        }
    };
}();

// Initialize chat for both the main and drawer chat containers when the DOM is fully loaded
KTUtil.onDOMContentLoaded(function () {
    // Initialize the chat for the main messenger and drawer chat containers
    KTAppChat.init(document.querySelector("#kt_chat_messenger"));
    KTAppChat.init(document.querySelector("#kt_drawer_chat_messenger"));
});
