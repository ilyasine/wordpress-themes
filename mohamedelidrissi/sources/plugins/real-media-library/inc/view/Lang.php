<?php

namespace MatthiasWeb\RealMediaLibrary\view;

use MatthiasWeb\RealMediaLibrary\Assets;
use MatthiasWeb\RealMediaLibrary\base\UtilsProvider;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * LEGACY Language texts for the JavaScript frontend. Later when using TypeScript
 * this class is no longer needed because the __() function is used and
 * automatically extracted.
 */
class Lang {
    use UtilsProvider;
    /**
     * Get an array of language keys and translations.
     *
     * @param Assets $assets
     * @return array
     */
    public function getItems($assets) {
        global $wpdb;
        // Check if already subfolder exist (in Lite Version)
        $liteSubfolderAdditionalText = '';
        if (!$this->isPro()) {
            $table_name = $this->getTableName();
            // phpcs:disable WordPress.DB.PreparedSQL
            $result = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE parent > -1");
            // phpcs:enable WordPress.DB.PreparedSQL
            if ($result > 0) {
                $liteSubfolderAdditionalText = \sprintf(
                    "\n\n*%s*",
                    __(
                        'Subfolders are only available in the PRO version. But you can now create an unlimited number of folders on the main level (instead of the previous max. 10 folders).',
                        RML_TD
                    )
                );
            }
        }
        return [
            'noneSelected' => __('No folder selected', RML_TD),
            'selectFolder' => __('Select folder', RML_TD),
            'reloadContent' => __('Reload content', RML_TD),
            'folder' => __('Folder', RML_TD),
            'subfolders' => __('Subfolders', RML_TD),
            'gutenBergBlockSelect' => __('Please select a folder (media library) in the block settings.', RML_TD),
            'warnDelete' => $assets->media_view_strings(\false),
            'restrictionsInherits' => __('New folders inherit this restriction', RML_TD),
            'restrictionsSuffix' => __('The current selected folder has some restrictions:', RML_TD),
            'restrictions.par' => __('You cannot change *parent* folder'),
            'restrictions.rea' => __('You cannot *rearrange* subfolders', RML_TD),
            'restrictions.cre' => __('You cannot *create* subfolders', RML_TD),
            'restrictions.ins' => __('You cannot *insert* new files. New files will be moved to Unorganized…', RML_TD),
            'restrictions.ren' => __('You cannot *rename* the folder', RML_TD),
            'restrictions.del' => __('You cannot *delete* the folder', RML_TD),
            'restrictions.mov' => __('You cannot *move* files outside the folder', RML_TD),
            'parent' => __('Parent', RML_TD),
            'beforeThisNode' => __('Before this node', RML_TD),
            'beforeThisNodeInfo' => __('If no next node is given, the element is placed at the end.', RML_TD),
            'uploaderUsesLeftTree' => __('The file is uploaded to the folder where you are currently in.', RML_TD),
            'areYouSure' => __('Are you sure?', RML_TD),
            'success' => __('Success'),
            'failed' => __('Failed'),
            'noEntries' => __('No entries found', RML_TD),
            'deleteConfirm' => __('Are you sure to delete *{name}*? All files gets moved to Unorganized.', RML_TD),
            'deleteMultipleConfirm' => __(
                'Are you sure to delete *{count} folders*? All files gets moved to Unorganized.',
                RML_TD
            ),
            'ok' => __('Ok'),
            'cancel' => __('Cancel'),
            'save' => __('Save'),
            'back' => __('Back'),
            'noFoldersTitle' => __('No folders found', RML_TD),
            'noFoldersDescription' => __(
                'No folders have been created yet. Just click on the button above to create your first folder.',
                RML_TD
            ),
            'folders' => __('Folders', RML_TD),
            'noSearchResult' => __('No search results.', RML_TD),
            'renameLoadingText' => __('Renaming to *{name}*…', RML_TD),
            'renameSuccess' => __('Successfully renamed folder to *{name}*', RML_TD),
            'addLoadingText' => __('Creating *{name}*…', RML_TD),
            'addSuccess' => __('Successfully created *{name}*', RML_TD),
            'deleteFailedSub' => __('The folder you try to delete has subfolders.', RML_TD),
            'deleteLoadingText' => __('Deleting *{name}*…', RML_TD),
            'deleteSuccess' => __('Successfully deleted *{name}*', RML_TD),
            'deleteMultipleSuccess' => __('Successfully deleted *{count} folders*', RML_TD),
            'sortByManual' => __('Rearrange *{name}* placement manually', RML_TD),
            'sortLoadingText' => __('Reordering the tree hierarchy…', RML_TD),
            'sortedSuccess' => __('Successfully sorted the tree hierarchy', RML_TD),
            'sortLoadingText' => __('Reorder subfolders of *{name}*…', RML_TD),
            'filesRemaining' => __('{count} files remaining…', RML_TD),
            'receiveData' => __('Receiving data…', RML_TD),
            'shortcut' => __('Shortcut', RML_TD),
            'shortcutInfo' => __(
                'This is a shortcut of a media library file. Shortcuts doesn\'t need any physical storage *(0 kB)*. If you want to change the file itself, you must do this in the original file (for example replace media file through a plugin).
Note also that the fields in the shortcuts can be different to the original file, for example "Title", "Description" or "Caption".',
                RML_TD
            ),
            'orderFilterActive' => __(
                'In the current view of uploads, filters are active. Please reset them and refresh the view.',
                RML_TD
            ),
            'uploadingCollection' => __('A collection cannot contain files. Upload moved to Unorganized…', RML_TD),
            'uploadingGallery' => __('A gallery can only contain images. Upload moved to Unorganized…', RML_TD),
            'orderLoadingText' => __('Order content by *{name}*…', RML_TD),
            'orderByDnd' => __('Order content by drag & drop', RML_TD),
            'resetOrder' => __('Reset order', RML_TD),
            'applyOrderOnce' => __('Apply order once…', RML_TD),
            'last' => __('Last', RML_TD),
            'deactivateOrderAutomatically' => __('Deactivate automatic ordering', RML_TD),
            'applyOrderAutomatically' => __('Apply automatic order…', RML_TD),
            'latest' => __('Latest', RML_TD),
            'reindexOrder' => __('Reindex order', RML_TD),
            'resetToLastOrder' => __('Reset to last order', RML_TD),
            'allPosts' => __('All files', RML_TD),
            'unorganized' => __('Unorganized', RML_TD),
            'move' => __('Move {count} files', RML_TD),
            'moveOne' => __('Move one file', RML_TD),
            'append' => __('Copy {count} files', RML_TD),
            'appendOne' => __('Copy one file', RML_TD),
            'moveLoadingText' => __('Moving {count} files…', RML_TD),
            'moveLoadingTextOne' => __('Moving one file…', RML_TD),
            'appendLoadingText' => __('Copying {count} files…', RML_TD),
            'appendLoadingTextOne' => __('Copying one file…', RML_TD),
            'moveSuccess' => __('Successfully moved {count} files', RML_TD),
            'moveSuccessOne' => __('Successfully moved one file', RML_TD),
            'appendSuccess' => __('Successfully copied {count} files', RML_TD),
            'appendSuccessOne' => __('Successfully copied one file', RML_TD),
            'moveTip' => __('Hold any key to create a shortcut', RML_TD),
            'appendTip' => __('Release key to move file', RML_TD),
            'creatable0ToolTipTitle' => __('Click this to create a new folder', RML_TD),
            'creatable0ToolTipText' => __(
                'A folder can contain any file type and collection, but not galleries. If you want to create a subfolder, select a folder and click this button.',
                RML_TD
            ),
            'creatable1ToolTipTitle' => __('Click this to create a new collection', RML_TD),
            'creatable1ToolTipText' => __(
                'A collection cannot contain files. However, you can create additional collections and *galleries* there. This gallery is only a *gallery data folder*, i.e. they are not automatically visible on the website.

You can create a *visual gallery* by using a shortcode in the Visual Editor on your pages/postings.',
                RML_TD
            ),
            'creatable2ToolTipTitle' => __('Click this to create a *new gallery data folder*', RML_TD),
            'creatable2ToolTipText' => __(
                'A *gallery data folder* can only contain images. It is easier for you to distinguish where your visual galleries are located.

You can also order the images into *a custom image order* per drag&drop.',
                RML_TD
            ),
            'userSettingsToolTipTitle' => __('Settings', RML_TD),
            'userSettingsToolTipText' => __('General settings for the current logged in user.', RML_TD),
            'lockedToolTipTitle' => __('Permissions', RML_TD),
            'orderToolTipTitle' => __('Reorder files in this folder', RML_TD),
            'orderToolTipText' => __('Start to reorder the files / images by *title, filename, ID, …*', RML_TD),
            'refreshToolTipTitle' => __('Refresh', RML_TD),
            'refreshToolTipText' => __('Refreshes the current folder view.', RML_TD),
            'renameToolTipTitle' => __('Rename', RML_TD),
            'renameToolTipText' => __('Rename the current selected folder.', RML_TD),
            'trashToolTipTitle' => __('Delete', RML_TD),
            'trashToolTipText' => __('Delete the current selected folder.', RML_TD),
            'trashMultipleToolTipText' => __('Delete the current selected folders.', RML_TD),
            'sortToolTipTitle' => __('Rearrange', RML_TD),
            'sortToolTipText' => __('Change the hierarchical order of the folders.', RML_TD),
            'detailsToolTipTitle' => __('Folder details', RML_TD),
            'detailsToolTipText' => __('Select a folder and view more details about it.', RML_TD),
            'defaultFolderNoneLabel' => __('Please select a folder to show items.', RML_TD),
            'noProductLicense' => __('Product license not yet activated.', RML_TD),
            'enterLicense' => __('Enter license', RML_TD),
            'licenseNoticeDismiss' => __('Dismiss notice', RML_TD),
            'sidebarDetectedTax' => __(
                'It looks like you have already used another plugin for folders in the media library.',
                RML_TD
            ),
            'sidebarDetectedTaxImport' => __('Start importing', RML_TD),
            'sidebarDetectedTaxDismiss' => __('Dismiss', RML_TD),
            'settingCopyLinkInfoRpm' => __(
                'Real Media Library creates a virtual folder structure. The URLs of uploads do not change when you move the file. Learn more about <a href="https://devowl.io/knowledge-base/how-can-i-physically-reflect-the-virtual-folder-structure-to-my-file-system/" target="_blank">how to automatically move files to physical folders.</a>',
                RML_TD
            ),
            'proRedirect' => __(
                'You will be redirected to the external website of PRO version. Please confirm to continue!',
                RML_TD
            ),
            'proFeature' => __('PRO Feature', RML_TD),
            'proDismiss' => __('Hide for 20 days', RML_TD),
            'proFooterText' => __('Thanks for using the free version of Real Media Library.', RML_TD),
            'proLearnMore' => __('Learn more about PRO', RML_TD),
            'proBoxTitle' => __('Get PRO!', RML_TD),
            'proBoxOk' => __('I want to learn more!', RML_TD),
            'proBoxCancel' => __('No, not interested…', RML_TD),
            'proFeatures' => [
                'collections' => [
                    'title' => __('You like collections?', RML_TD),
                    'image' => 'collections.jpg',
                    'description' => __(
                        'Get more organized with different types of folders: Collections and galleries help you to easily recognize where your image galleries are located.',
                        RML_TD
                    )
                ],
                'order-content' => [
                    'title' => __('Custom content order?', RML_TD),
                    'image' => 'order-content.gif',
                    'description' => __(
                        'Get your folder contents in order and arrange your files according to a criterion (e.g. name descending) or by drag & drop.',
                        RML_TD
                    )
                ],
                'order-subfolders' => [
                    'title' => __('Full order control?', RML_TD),
                    'image' => 'order-subfolder.gif',
                    'description' => __(
                        'Organize the nodes within your folder tree according to a criterion (e.g. name descending) or by drag & drop.',
                        RML_TD
                    )
                ],
                'subfolder' => [
                    'title' => __('Want to create subfolders?', RML_TD),
                    'image' => 'full-control.gif',
                    'description' =>
                        __(
                            'Subfolders offer you the possibility to bring more structure into your media library. They help you to keep the overview, even if you really have many files in your media library.',
                            RML_TD
                        ) . $liteSubfolderAdditionalText
                ],
                'insert-media-tree-view' => [
                    'title' => __('Want to switch between folders more comfortably?', RML_TD),
                    'image' => 'inserting-media-dialog.gif',
                    'description' => __(
                        'Let the complete folder tree as in your media library also be displayed in this dialog instead of searching each time in the dropdown. It is simply more comfortable!',
                        RML_TD
                    )
                ],
                'recursive-upload' => [
                    'title' => __('Want to upload entire folders?', RML_TD),
                    'description' => __(
                        'With Real Media Library you can upload entire folders using drag and drop. All folders, subfolders and files will be uploaded and displayed in your media library in the same structure. Get PRO to upload folders!',
                        RML_TD
                    )
                ]
            ]
        ];
    }
}
