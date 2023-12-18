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

    /**
     * Notifications for the client
     */
    case ProjectRegistered = 'PROJECT_REGISTERED';
    case ProjectReadyForReview = 'PROJECT_READY_FOR_REVIEW';

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

    /**
     * Notifications related to account management
     */
    case InstitutionUserCreated = 'INSTITUTION_USER_CREATED';
    case InstitutionUserActivated = 'INSTITUTION_USER_ACTIVATED';
}