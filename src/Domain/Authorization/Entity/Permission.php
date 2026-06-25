<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Entity;

enum Permission: string
{
    // Customers
    case CustomerList   = 'customer.list';
    case CustomerView   = 'customer.view';
    case CustomerCreate = 'customer.create';
    case CustomerUpdate = 'customer.update';
    case CustomerDelete = 'customer.delete';

    // Projects
    case ProjectList          = 'project.list';
    case ProjectView          = 'project.view';
    case ProjectCreate        = 'project.create';
    case ProjectUpdate        = 'project.update';
    case ProjectDelete        = 'project.delete';
    case ProjectAssignMembers = 'project.assign_members';

    // Invoices
    case InvoiceList   = 'invoice.list';
    case InvoiceView   = 'invoice.view';
    case InvoiceCreate = 'invoice.create';
    case InvoiceUpdate = 'invoice.update';
    case InvoiceDelete = 'invoice.delete';

    // Quotes
    case QuoteList   = 'quote.list';
    case QuoteView   = 'quote.view';
    case QuoteCreate = 'quote.create';
    case QuoteUpdate = 'quote.update';
    case QuoteDelete = 'quote.delete';

    // Time Tracking
    case TimeRecordList        = 'time_record.list';
    case TimeRecordView        = 'time_record.view';
    case TimeRecordCreate      = 'time_record.create';
    case TimeRecordUpdate      = 'time_record.update';
    case TimeRecordDelete      = 'time_record.delete';
    case TimeRecordViewAll     = 'time_record.view_all';
    case TimeRecordManageAll   = 'time_record.manage_all';
    case TimeRecordViewSummary = 'time_record.view_summary';

    // Users
    case UserList   = 'user.list';
    case UserView   = 'user.view';
    case UserCreate = 'user.create';
    case UserUpdate = 'user.update';
    case UserDelete = 'user.delete';

    // Roles
    case RoleList   = 'role.list';
    case RoleView   = 'role.view';
    case RoleCreate = 'role.create';
    case RoleUpdate = 'role.update';
    case RoleDelete = 'role.delete';

    // System
    case SystemView   = 'system.view';
    case SystemManage = 'system.manage';

    // Link Shortener
    case LinkShortenerView   = 'link_shortener.view';
    case LinkShortenerManage = 'link_shortener.manage';

    public function actionLabel(): string
    {
        return match ($this) {
            self::ProjectAssignMembers  => 'Assign Members',
            self::TimeRecordViewAll     => 'View All',
            self::TimeRecordManageAll   => 'Manage All',
            self::TimeRecordViewSummary => 'Summary',
            default                     => match (true) {
                str_ends_with($this->value, '.list')   => 'List',
                str_ends_with($this->value, '.view')   => 'View',
                str_ends_with($this->value, '.create') => 'Create',
                str_ends_with($this->value, '.update') => 'Update',
                str_ends_with($this->value, '.delete') => 'Delete',
                default                                => $this->name,
            },
        };
    }

    /** @return array<string, self[]> */
    public static function groupedByResource(): array
    {
        return [
            'Customers' => [
                self::CustomerList, self::CustomerView, self::CustomerCreate,
                self::CustomerUpdate, self::CustomerDelete,
            ],
            'Projects' => [
                self::ProjectList, self::ProjectView, self::ProjectCreate,
                self::ProjectUpdate, self::ProjectDelete, self::ProjectAssignMembers,
            ],
            'Invoices' => [
                self::InvoiceList, self::InvoiceView, self::InvoiceCreate,
                self::InvoiceUpdate, self::InvoiceDelete,
            ],
            'Quotes' => [
                self::QuoteList, self::QuoteView, self::QuoteCreate,
                self::QuoteUpdate, self::QuoteDelete,
            ],
            'Time Tracking' => [
                self::TimeRecordList, self::TimeRecordView, self::TimeRecordCreate,
                self::TimeRecordUpdate, self::TimeRecordDelete,
                self::TimeRecordViewAll, self::TimeRecordManageAll, self::TimeRecordViewSummary,
            ],
            'Users' => [
                self::UserList, self::UserView, self::UserCreate,
                self::UserUpdate, self::UserDelete,
            ],
            'Roles' => [
                self::RoleList, self::RoleView, self::RoleCreate,
                self::RoleUpdate, self::RoleDelete,
            ],
            'System' => [
                self::SystemView, self::SystemManage,
            ],
            'Link Shortener' => [
                self::LinkShortenerView, self::LinkShortenerManage,
            ],
        ];
    }
}
