/**
 * Alka Chat Vue Application
 * Render alka chat chat through Vue.js
 *
 * @author Alkaweb Team
 */
var alkaChat = function () {

    var self = this;

    self.wrapper = Woffice.$body.find('#alka-chat-wrapper');
    self.$ = Woffice.$;

    /**
     * We define some useful helpers
     * @type {*}
     */
    if(self.wrapper.length === 0) {
        return;
    }

    /**
     * Format day filter
     */
    Vue.filter('formatDate', function(value) {
        if (value) {
            var date = new Date(value);
            return date.toLocaleDateString();
        }
    });

    /**
     * Format time filter
     */
    Vue.filter('formatTime', function(value) {
        if (value) {
            var date = new Date(value);
            return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
        }
    });

    /**
     * Vue Root
     * @type {Vue}
     */
    self.chat = new Vue({

        el: self.wrapper[0],

        /**
         * Data Wrapper
         */
        data: {

            isLoading: false,
            loader: null,

            isOpen: false, // open or other states in the future

            conversations: {},
            conversationPage: 1,
            perPage: 15,

            refreshInterval: null,

            exchanger: Woffice.data.alka_chat,

            showNewConversation: false,
            newConversationSearch: '',
            newConversationPotentialParticipants: [],
            newConversationParticipants: [],
            newConversationTitle: '',

            showCustomTab: false,

            showCurrentConversation: false,
            currentConversation: null,
            currentMessages: [],
            currentMessagesPage: 1,
            newMessage: '',

            alert: null,

            actions: Woffice.data.alka_chat.actions,
            currentAction: ''

        },

        filters: {
            json: function(value) {
                return value.replace('\\\'s', '\'s');
            },
            title: function (value, n) {
                if (value.length <= n)
                    return value;
                var subString = value.substr(0, n-1);
                return subString.substr(0, subString.lastIndexOf(' ')) + "...";
            }
        },

        created: function() {

            var self = this;

            // Create a Spinner.js instance
            self.loader = new Spinner({
                color: "#333",
                scale: 0.6,
                left: "98%"
            });

            // Set the page
            Woffice.$(window).on("resize", function () {
                self.setPerPage();
            }).resize();

            self.customTab('start');

        },

        watch: {

            /**
             * Handle the main layout change when the right bottom main button is clicked
             *
             * @param {string} newState
             */
            isOpen: function (newState) {

                var self = this;

                if(newState) {
                    self.refreshInterval = setInterval(self.refresh,self.exchanger.refresh_time);
                    self.fetchConversations();
                } else {
                    clearInterval(self.refreshInterval);
                }

            },

            /**
             * Adds the Spin.js spinner on loading state change
             * @param {boolean }newState
             */
            isLoading: function (newState) {
                var self = this;
                if(typeof self.loader === 'undefined')
                    return;
                if(newState === true)
                    self.loader.spin(Woffice.$.find('#alka-chat-alerts')[0]);
                else
                    self.loader.stop();
            },

            /**
             * Removes automatically an alert after 5sec
             */
            alert: function () {
                var self = this;
                setTimeout(function () {
                    self.alert = null;
                }, 5000);
            },

            /**
             * On new participant typing
             *
             * @param {string} newVal
             */
            newConversationSearch: function (newVal) {

                if(newVal.length < 3)
                    return;

                var self = this;

                self.autoFetchMembers();

            }
            
        },

        methods: {

            /**
             * Toggles the meta line for each message
             *
             * @param {object} messageObj
             */
            toggleMeta: function (messageObj) {

                var self = this;

                if(messageObj.sender_id !== self.exchanger.current_user)
                    return;

                messageObj._showMeta = (messageObj._showMeta !== true);

                self.$forceUpdate();

            },

            /**
             * Toggles the edit form for a message
             *
             * @param {object} messageObj
             */
            toggleMessageEdit: function (messageObj) {

                var self = this;

                messageObj._showEdit = (messageObj._showEdit !== true);

                self.$forceUpdate();

            },

            /**
             * Sends a message to the current conversation
             */
            sendMessage: function () {

                var self = this,
                    returned;

                if(self.newMessage.length === 0)
                    return;

                self.isLoading = true;

                // We get the IP first
                Woffice.$.getJSON('//freegeoip.net/json/?callback=?').done(function(userData) {

                    if(typeof userData !== 'object')
                        return;

                    var IP = userData.ip;

                    self.apiRequest('POST', 'conversations/'+self.currentConversation.id+'/post', {
                        message: self.newMessage,
                        sender_id: self.exchanger.current_user,
                        sender_ip: IP
                    }).done(function(result) {

                        self.isLoading = false;
                        returned = JSON.parse(result);

                        self.alert = {
                            type: returned.type,
                            message: returned.message
                        };

                        if(returned.type !== 'success')
                            return;

                        self.newMessage = '';

                        self.showConversation(self.currentConversation);


                    });
                });




            },

            /**
             * Deletes a given message
             *
             * @param {object} messageObj
             */
            deleteMessage: function (messageObj) {

                var self = this,
                    returned;

                self.isLoading = true;

                self.apiRequest('POST', 'messages/'+messageObj.id+'/delete', {}).done(function(result) {

                    self.isLoading = false;

                    returned = JSON.parse(result);

                    self.alert = returned;

                    self.showConversation(self.currentConversation);

                });

            },

            /**
             * Edits a given message
             *
             * @param {object} messageObj
             */
            saveMessage: function (messageObj) {

                var self = this,
                    returned;

                self.isLoading = true;

                self.apiRequest('POST', 'messages/'+messageObj.id+'/edit', {
                    message: messageObj.message
                }).done(function(result) {

                    self.isLoading = false;
                    returned = JSON.parse(result);

                    self.alert = returned;

                    self.showConversation(self.currentConversation);

                });

            },

            /**
             * Displays a single conversation
             *
             * @param {object} conversation
             * @param {bool} isInRefresh
             */
            showConversation: function (conversation, isInRefresh) {

                var self = this,
                    returned;

                isInRefresh = (typeof isInRefresh === 'undefined') ? false : isInRefresh;

                // We remove the has_new element
                self.conversations.data.forEach(function (element, index) {
                    if(element.id === conversation.id)
                        self.conversations.data[index].has_new = false;
                });

                if(!isInRefresh) self.isLoading = true;

                self.apiRequest('GET', 'conversations/'+conversation.id, {
                    page: self.currentMessagesPage
                }).done(function(result) {

                    self.isLoading = false;

                    returned = JSON.parse(result);
                    if(returned.type === 'error') {
                        self.alert = returned;
                        return;
                    }

                    self.showCurrentConversation = true;
                    self.currentConversation = returned.conversation;
                    if(self.currentMessagesPage === 1) {
                        self.currentMessages = returned.messages;
                        self.currentMessages.data.reverse();
                    } else {
                        self.currentMessages.current_page = returned.messages.current_page;
                        self.currentMessages.next_page_url = returned.messages.next_page_url;
                        self.currentMessages.data = returned.messages.data.concat(self.currentMessages.data);
                    }

                    // We set the tooltips and popovers
                    setTimeout(function () {

                        // Tooltips
                        Woffice.tooltips.start();

                        // Popover
                        var $popover = Woffice.$('.show-conversation-meta');
                        $popover.attr('data-content', Woffice.$('.conversation-meta-wrapper').html());
                        $popover.popover({
                            html:"true",
                            placement:"bottom",
                            content:"Hey"
                        }).on('show.bs.popover', function () {
                            setTimeout(function () {
                                Woffice.tooltips.start();
                                Woffice.$('.conversation-meta').find('a.btn.btn-danger').on('click', function () {
                                    self.deleteConversation(self.currentConversation);
                                });
                            }, 200);
                        });

                        if( self.exchanger.has_emojis ) {
                            // Emoji Picker
                            Woffice.$('.alka-chat-new-message-wrapper textarea').emojiPicker({
                                width: '300px',
                                height: '200px',
                                iconBackgroundColor: '#e4e4e8',
                                iconColor: 'black'
                            });
                        }

                        // Scroll down
                        setTimeout(function () {
                            var $modalBody = Woffice.$('.alka-chat-modal-body'),
                                height = $modalBody[0].scrollHeight;
                            $modalBody.scrollTop(height);
                        }, 200);

                    }, 1000);

                });

            },

            /**
             * Closes the current conversation modal
             */
            closeCurrentConversation: function () {
                var self = this;
                self.showCurrentConversation = false;
                self.currentConversation = null
            },

            /**
             * Paginates the messages
             *
             * @param {integer} index
             */
            messagesPaginate: function (index) {

                var self = this;

                self.currentMessagesPage = self.currentMessagesPage + index;
                self.showConversation(self.currentConversation);

            },

            /**
             * Sets the a member to the new conversation participant IDs array
             * This is triggered when the member is clicked from the auto-select list
             *
             * @param {object} member
             */
            setConversationParticipant: function(member) {

                var self = this;

                self.newConversationParticipants.push(member);
                self.newConversationSearch = '';
                self.newConversationPotentialParticipants = [];
                if(self.newConversationTitle.length === 0) {
                    self.newConversationTitle = self.exchanger.labels.new_conversation_title;
                    self.newConversationTitle = self.newConversationTitle + ' ' + member.label;
                } else {
                    self.newConversationTitle = self.newConversationTitle + ', ' + member.label;
                }

            },

            /**
             * Deletes a given conversation
             *
             * @param {object} conversation
             */
            deleteConversation: function (conversation) {

                var self = this,
                    returned;

                self.isLoading = true;

                self.apiRequest('POST', 'conversations/'+conversation.id+'/delete', {}).done(function(result) {

                    self.isLoading = false;
                    returned = JSON.parse(result);

                    self.alert = returned;

                    self.showCurrentConversation = false;
                    self.currentConversation = null;
                    self.currentMessages = [];

                    self.fetchConversations();

                });

            },

            /**
             * Creates a new conversation
             */
            newConversation: function () {

                var self = this,
                    returned;

                if(self.newConversationParticipants.length === 0 || self.newConversationTitle.length === 0)
                    return;

                self.isLoading = true;

                // We only send the IDs to the API
                var participants = [];
                participants.push(self.exchanger.current_user);
                self.newConversationParticipants.forEach(function (participant) {
                    participants.push(participant.value);
                });

                self.apiRequest('POST', 'conversations/create', {
                    'meta': {
                        'title': self.newConversationTitle,
                        'author': self.exchanger.current_user
                    },
                    'participants': participants
                }).done(function(result) {

                    self.isLoading = false;
                    returned = JSON.parse(result);

                    self.alert = returned;

                    if(returned.type !== 'success')
                        return;

                    self.newConversationTitle = '';
                    self.newConversationParticipants = [];
                    self.showNewConversation = false;
                    self.fetchConversations();

                });

            },

            /**
             * Fetch all conversations for the current user from the backend
             *
             * @param {bool} isInRefresh
             */
            fetchConversations: function (isInRefresh) {

                var self = this,
                    returned;

                isInRefresh = (typeof isInRefresh === 'undefined') ? false : isInRefresh;

                if(!isInRefresh) self.isLoading = true;

                self.apiRequest('GET', 'conversations', {
                    'page': (self.conversationPage),
                    'per_page': (self.perPage),
                    'user': self.exchanger.current_user
                }).done(function(result) {

                    self.isLoading = false;

                    returned = JSON.parse(result);
                    if(returned.type === 'error') {
                        self.alert = returned;
                        return;
                    }

                    self.conversations = returned.conversations;

                    setTimeout(function () {
                        Woffice.tooltips.start();
                    }, 1000);

                });

            },

            /**
             * Paginates the conversation
             *
             * @param {integer} index
             */
            conversationsPaginate: function (index) {

                var self = this;

                self.conversationPage = self.conversationPage + index;
                self.fetchConversations();

            },

            /**
             * Changes the chat wrapper state
             */
            switchState: function () {

                var self = this;

                self.isOpen = !self.isOpen;

            },

            /**
             * Starts an action
             *
             * @param {string} actionId
             */
            startAction: function (actionId) {

                var self = this;

                self.currentAction = actionId;

                if(actionId === 'new_conversation') {
                    self.showCustomTab = false;
                    self.showNewConversation = !self.showNewConversation;
                } else if(actionId === 'refresh') {
                    self.refresh();
                } else if(actionId === 'custom_tab') {
                    self.showNewConversation = false;
                    self.customTab('show');
                }

            },

            /**
             * Refresh the chat
             */
            refresh: function () {
                var self = this;
                self.fetchConversations(true);
                if(self.currentConversation) {
                    self.showConversation(self.currentConversation, true);
                }
            },

            /**
             * Make an API request to the server (which will forward it)
             *
             * @param {string} method
             * @param {string} target
             * @param {object} payload
             */
            apiRequest: function (method, target, payload) {

                return Woffice.$.ajax({
                    url: Woffice.data.ajax_url.toString(),
                    type: 'POST',
                    data: {
                        'action': 'woffice_alka_chat',
                        '_nonce': Woffice.data.alka_chat.nonce,
                        'api_method': method,
                        'api_target': target,
                        'api_payload': payload
                    }
                });

            },

            /**
             * Auto populates the member list on typing
             */
            autoFetchMembers: function () {

                var self = this;

                self.isLoading = true;

                if(self.newConversationSearch.length < 3)
                    return;

                Woffice.$.ajax({
                    url: Woffice.data.ajax_url.toString(),
                    type: 'GET',
                    data: {
                        'action': 'woffice_members_suggestion_autocomplete',
                        'term': self.newConversationSearch
                    },
                    success: function (result) {
                        self.isLoading = false;
                        var potentielParticipants = JSON.parse(result),
                            validatedParticipants = [];
                        potentielParticipants.forEach(function (participant) {
                            var alreadyThere = false;
                            for(var i = 0; i < self.newConversationParticipants.length; i++) {
                                if (self.newConversationParticipants[i].value === participant.value) {
                                    alreadyThere = true;
                                    break;
                                }
                            }
                            // If not current user & not already present
                            if(parseInt(participant.value) !== self.exchanger.current_user && !alreadyThere) {
                                validatedParticipants.push(participant);
                            }
                        });
                        self.newConversationPotentialParticipants = validatedParticipants;
                    }
                });

            },

            /**
             * Set the number of conversations loaded per page according to page width
             */
            setPerPage: function () {

                var self = this;

                // The button and spacing:
                var margin = 350;

                // 90px is the size of the conversation thumbnail + margin
                self.perPage = Math.round((window.innerWidth - margin) / 90)

            },

            /**
             * Returns the message sender's Avatar HTML for the current conversation
             *
             * @param {integer} sender_id
             */
            getAvatar: function (sender_id) {

                var self = this,
                    avatar = '';

                if(!self.currentConversation)
                    return avatar;
                
                self.currentConversation.participants.forEach(function (participant) {
                   if(parseInt(participant._id) === parseInt(sender_id))
                       avatar = participant._avatar;
                });

                return avatar;

            },

            /**
             * Handles the custom tab state
             * It's displayed by default if it exist and we save the state into the local storage
             *
             * @param {string} action - start, show, hide
             */
            customTab: function (action) {

                var self = this;

                if (typeof(Storage) === "undefined")
                    return;

                var wofficeData = JSON.parse(localStorage.getItem("woffice"));

                var currentState = (wofficeData !== null && typeof(wofficeData.alka_chat_state) !== 'undefined') ? wofficeData.alka_chat_state : false;

                if(action === 'start') {
                    if(currentState === false || currentState === 'not_displayed') {
                        self.showCustomTab = true;
                    }
                } else if (action === 'show') {
                    self.showCustomTab = true;
                } else {
                    self.showCustomTab = false;
                }

                var newState = 'displayed';

                if(wofficeData === null)
                    wofficeData = {};

                wofficeData.alka_chat_state = newState;

                // We save it back
                localStorage.setItem("woffice", JSON.stringify(wofficeData));

            }

        }

    });

};

/*
 * Start it up!
 */
new alkaChat();