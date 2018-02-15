<div id="alka-chat-wrapper" :class="{'is-open' : isOpen}" v-cloak>

    <div id="alka-chat-conversations-wrapper">

        <div id="alka-chat-alerts">
            <div v-if="alert" :class="'tiny-alert tiny-alert-'+alert.type" v-text="alert.message"></div>
        </div>

        <transition name="slide-fade-top">
            <!-- New conversation -->
            <div v-if="showNewConversation" id="new-conversation" class="alka-chat-modal">
                <div class="alka-chat-modal-inner">
                    <div class="alka-chat-modal-header" class="clearfix">
                        <h3 class="pull-left" v-text="actions.new_conversation"></h3>
                        <a href="#" @click.prevent="showNewConversation = false" class="modal-close pull-right"><i class="fa fa-times-circle"></i></a>
                    </div>
                    <div class="alka-chat-modal-body">
                        <div class="auto-fetch-members-wrapper">
                            <label v-text="exchanger.labels.new_conversation_conversations_placeholder"></label>
                            <input type="text" v-model="newConversationSearch">
                            <ul v-if="newConversationPotentialParticipants" class="potential-participants">
                                <li v-for="member in newConversationPotentialParticipants" @click="setConversationParticipant(member)" v-text="member.label"></li>
                            </ul>
                        </div>
                        <ul class="conversations-participant">
                            <li v-for="(participant, index) in newConversationParticipants">
                                <span v-text="participant.label"></span>
                                <a href="#" @click.prevent="newConversationParticipants.splice(index, 1)" class="fa fa-times"></a>
                            </li>
                        </ul>
                        <div>
                            <label v-text="exchanger.labels.new_conversation_title_label"></label>
                            <input type="text" v-model="newConversationTitle">
                        </div>
                    </div>
                    <div class="alka-chat-modal-footer">
                        <a href="#" @click.prevent="newConversation()" class="btn btn-default">
                            <i class="fa fa-plus-square"></i>
                            <span v-text="exchanger.labels.new_conversation"></span>
                        </a>
                    </div>
                </div>
            </div>
            <!-- Single conversation -->
            <div v-if="showCurrentConversation" id="single-conversation" class="alka-chat-modal">
                <div class="alka-chat-modal-inner">
                    <div class="alka-chat-modal-header">
                        <div class="clearfix">
                            <h3 v-if="currentConversation.meta.title"
                                class="pull-left"
                                :title="currentConversation.meta.title"
                                data-toggle="tooltip"
                                data-placement="top"
                                v-html="$options.filters.title(currentConversation.meta.title, 35)"></h3>
                            <a href="#"
                               class="pull-left show-conversation-meta"
                               data-toggle="popover"
                            ><i class="fa fa-info-circle"></i></a>
                            <a href="#" @click.prevent="closeCurrentConversation()" class="pull-right"><i class="fa fa-times-circle"></i></a>
                        </div>
                        <div class="conversation-meta-wrapper hide">
                            <div class="conversation-meta">
                                <ul class="conversation-participants list-inline">
                                    <li v-for="participant in currentConversation.participants">
                                        <a :href="participant._profile" :title="participant._name" data-toggle="tooltip" data-placement="top">
                                            <span v-html="participant._avatar"></span>
                                        </a>
                                    </li>
                                </ul>
                                <hr>
                                <ul class="list-inline">
                                    <li class="conversation-date">
                                        <i class="fa fa-clock-o"></i> {{ currentConversation.created_at | formatDate }}
                                    </li>
                                    <li v-if="currentMessages" class="conversation-messages">
                                        <i class="fa fa-comments"></i> {{ currentMessages.total }}
                                    </li>
                                </ul>
                                <hr>
                                <div v-if="exchanger.current_user == currentConversation.meta.author" class="text-center">
                                    <a href="#" class="btn btn-danger">
                                        <i class="fa fa-trash-o"></i> <?php _e('Delete', 'woffice'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="alka-chat-modal-body">
                        <div v-if="(currentMessages && currentMessages.total > 0)" class="alka-chat-messages-holder">
                            <div v-if="currentMessages.next_page_url" class="alka-chat-messages-pagination text-center">
                                <a href="#" @click.prevent="messagesPaginate(1)" class="btn btn-default"><?php _e('Load previous messages', 'woffice'); ?></a>
                            </div>
                            <ul class="list-unstyled">
                                <li v-for="(messageObj, messageIndex) in currentMessages.data"
                                    :id="'alka-chat-message-'+messageObj.id"
                                    class="alka-chat-message clearfix"
                                    :key="messageObj.id"
                                    :class="{ 'current-user': (exchanger.current_user == messageObj.sender_id) }">
                                    <div class="alka-chat-message-content">
                                        <p v-if="!messageObj._showEdit" :class="{ 'woffice-colored-bg': (exchanger.current_user == messageObj.sender_id) }" @click.prevent="toggleMeta(messageObj)">
                                            {{ messageObj.message | json }}
                                        </p>
                                        <textarea v-else v-model="messageObj.message"></textarea>
                                        <div v-show="messageObj._showMeta" class="alka-chat-message-actions">
                                            <ul class="list-inline">
                                                <li>
                                                    <i class="fa fa-check"></i> {{ messageObj.created_at | formatTime }}
                                                </li>
                                                <li>
                                                    <a href="#" @click.prevent="deleteMessage(messageObj)">
                                                        <i class="fa fa-trash"></i>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#" @click.prevent="toggleMessageEdit(messageObj)">
                                                        <i v-if="!messageObj._showEdit" class="fa fa-pencil"></i>
                                                        <i v-else class="fa fa-times"></i>
                                                    </a>
                                                </li>
                                                <li v-if="messageObj._showEdit">
                                                    <a href="#" @click.prevent="saveMessage(messageObj)">
                                                        <i class="fa fa-floppy-o"></i>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="alka-chat-message-thumb" v-html="getAvatar(messageObj.sender_id)"></div>
                                </li>
                            </ul>
                        </div>
                        <div v-else class="messages-empty text-center">
                            <i class="fa fa-5x fa-meh-o"></i>
                            <span v-text="exchanger.labels.not_found"></span>
                        </div>
                    </div>
                    <div class="alka-chat-modal-footer clearfix">
                        <div class="alka-chat-new-message-wrapper pull-left">
                            <textarea class="form-control" @keyup.enter="sendMessage()" v-model="newMessage" placeholder="<?php _e('Type your message here', 'woffice'); ?>"></textarea>
                        </div>
                        <a href="#" @click.prevent="sendMessage()" class="btn btn-default pull-right" :class="{'disabled' : isLoading}">
                            <i class="fa fa-paper-plane-o"></i>
                            <span v-text="exchanger.labels.send"></span>
                        </a>
                    </div>
                </div>
            </div>
            <!-- Custom tab -->
            <div v-if="showCustomTab" id="custom-tab" class="alka-chat-modal">
                <div class="alka-chat-modal-inner">
                    <div class="alka-chat-modal-header" class="clearfix">
                        <h3 class="pull-left" v-html="actions.custom_tab"></h3>
                        <a href="#" @click.prevent="customTab('hide')" class="modal-close pull-right"><i class="fa fa-times-circle"></i></a>
                    </div>
                    <div class="alka-chat-modal-body" v-html="exchanger.custom_tab"></div>
                </div>
            </div>
        </transition>

        <div v-if="conversations" id="alka-chat-conversations" class="pull-left">
            <transition-group tag="ul" name="slide-fade-right" class="list-unstyled">
                <li v-if="conversations.next_page_url" class="conversation-pagination" :key="'next_page'">
                    <a href="#" @click.prevent="conversationsPaginate(1)">
                        <i class="fa fa-angle-double-left page-switcher"></i>
                    </a>
                </li>
                <li v-if="conversations.prev_page_url" class="conversation-pagination" :key="'prev_page'">
                    <a href="#" @click.prevent="conversationsPaginate(-1)">
                        <i class="fa fa-angle-double-right page-switcher"></i>
                    </a>
                </li>
                <li v-for="conversation in conversations.data" class="single-conversation" :key="conversation.id">
                    <a href="#" @click.prevent="showConversation(conversation)" :title="(conversation.meta !== null) ? conversation.meta.title : ''" data-toggle="tooltip" data-placement="top">
                        <!-- -2 for the current user and displayed one -->
                        <span class="participants conversation-label" v-if="conversation.participants.length > 2">+ {{ (conversation.participants.length - 2) }}</span>
                        <span v-if="conversation.has_new" class="new conversation-label"><i class="fa fa-circle"></i></span>
                        <span v-html="conversation.participants[0]._avatar"></span>
                    </a>
                </li>
            </transition-group>
        </div>

    </div>

    <div id="alka-chat-actions">

        <div v-show="isOpen">
            <transition-group tag="ul" name="slide-fade-bottom" class="list list-unstyled">
                <li v-for="(action, actionID) in actions" :key="actionID">
                    <a href="#" @click.prevent="startAction(actionID)" v-html="action"></a>
                </li>
            </transition-group>
        </div>

        <transition name="slide-fade-bottom">
            <a v-if="!isOpen" href="#" id="alka-chat-main" class="woffice-colored-bg" @click.prevent="switchState()" key="open">
                <i class="fa fa-comment-o"></i>
            </a>
            <a v-else href="#" id="alka-chat-main" class="woffice-colored-bg" @click.prevent="switchState()" key="close">
                <i class="fa fa-times"></i>
            </a>
        </transition>

    </div>

</div>