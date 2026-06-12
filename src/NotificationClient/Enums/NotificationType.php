<?php

namespace NotificationClient\Enums;

enum NotificationType: string
{
    /**
     * Notifications for the translation project manager (PM)
     */
    case ProjectCreated = 'PROJECT_CREATED';
    case ProjectSentToClient = 'PROJECT_SENT_TO_CLIENT';
    case SubProjectSentToPm = 'SUBPROJECT_SENT_TO_PM';
    case ProjectRejected = 'PROJECT_REJECTED';
    case TaskDeclinedByVendor = 'TASK_DECLINED_BY_VENDOR';
    case ReactionTimeExpired = 'REACTION_TIME_EXPIRED';
    case NoExternalVendorsAvailable = 'NO_EXTERNAL_VENDORS_AVAILABLE';
    case ProjectTimeslotPassedWithNoAssignee = 'PROJECT_TIMESLOT_PASSED_WITH_NO_ASSIGNEE';
    case VendorWasNotAssignedAutomatically = 'VENDOR_WAS_NOT_ASSIGNED_AUTOMATICALLY';

    /**
     * Notifications for the client
     */
    case ProjectRegistered = 'PROJECT_REGISTERED';
    case ProjectReadyForReview = 'PROJECT_READY_FOR_REVIEW';
    case ProjectUpdated = 'PROJECT_UPDATED';

    /**
     * Notifications that are common for client and PM
     */
    case InstitutionUserAssignedToProject = 'INSTITUTION_USER_ASSIGNED_TO_PROJECT';
    case TaskAccepted = 'TASK_ACCEPTED';
    case SubProjectTaskMarkedAsDone = 'SUBPROJECT_TASK_MARKED_AS_DONE';
    case ProjectAccepted = 'PROJECT_ACCEPTED';
    case ProjectDeadlineReached = 'PROJECT_DEADLINE_REACHED';
    case ProjectCancelled = 'PROJECT_CANCELLED';

    /**
     * Notifications for the vendor
     */
    case TaskCreated = 'TASK_CREATED';
    case TaskRejected = 'TASK_REJECTED';
    case TaskCancelled = 'TASK_CANCELLED';
    case TaskUpdated = 'TASK_UPDATED';

    /**
     * Notifications related to account management
     */
    case InstitutionUserCreated = 'INSTITUTION_USER_CREATED';
    case InstitutionUserActivated = 'INSTITUTION_USER_ACTIVATED';

    /**
     * Notifications related to outsource requests
     */
    case OutsourceOfferRequestSent      = 'OUTSOURCE_OFFER_REQUEST_SENT';
    case OutsourceOfferRequestAccepted  = 'OUTSOURCE_OFFER_REQUEST_ACCEPTED';
    case OutsourceOfferRequestDeclined  = 'OUTSOURCE_OFFER_REQUEST_DECLINED';
    case OutsourceOfferRequestExpired   = 'OUTSOURCE_OFFER_REQUEST_EXPIRED';
    case OutsourceOfferDeclined         = 'OUTSOURCE_OFFER_DECLINED';
    case OutsourceOfferAccepted         = 'OUTSOURCE_OFFER_ACCEPTED';
    case OutsourceRequestCancelled      = 'OUTSOURCE_REQUEST_CANCELLED';
}