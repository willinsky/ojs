<?php
/**
 * @file classes/components/form/publication/IssueEntryForm.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class IssueEntryForm
 * @ingroup classes_controllers_form
 *
 * @brief A preset form for setting a publication's issue, section, categories,
 *  pages, etc.
 */
namespace APP\components\forms\publication;
use \PKP\components\forms\FormComponent;
use \PKP\components\forms\FieldSelect;
use \PKP\components\forms\FieldText;
use \PKP\components\forms\FieldUploadImage;

define('FORM_ISSUE_ENTRY', 'issueEntry');

class IssueEntryForm extends FormComponent {
	/** @copydoc FormComponent::$id */
	public $id = FORM_ISSUE_ENTRY;

	/** @copydoc FormComponent::$method */
	public $method = 'PUT';

	/**
	 * Constructor
	 *
	 * @param $action string URL to submit the form to
	 * @param $locales array Supported locales
	 * @param $publication Publication The publication to change settings for
	 * @param $publicationContext Context The context of the publication
	 * @param $baseUrl string Site's base URL. Used for image previews.
	 * @param $temporaryFileApiUrl string URL to upload files to
	 */
	public function __construct($action, $locales, $publication, $publicationContext, $baseUrl, $temporaryFileApiUrl) {
		$this->action = $action;
		$this->successMessage = __('publication.issueEntry.success');
		$this->locales = $locales;

		// Issue options
		$futureIssues = \Services::get('issue')->getMany([
			'contextId' => $publicationContext->getId(),
			'isPublished' => false,
		]);
		$backIssues = \Services::get('issue')->getMany([
			'contextId' => $publicationContext->getId(),
			'isPublished' => true,
		]);
		$issueOptions = [
			[
				'value' => '',
				'label' => '------ ' . __('editor.issues.futureIssues') . ' ------',
			]
		];
		foreach ($futureIssues as $issue) {
			$issueOptions[] = [
				'value' => $issue->getId(),
				'label' => $issue->getIssueIdentification(),
			];
		}
		$issueOptions[] = [
			'value' => '',
			'label' => '------ ' . __('editor.issues.currentIssue') . ' ------',
		];
		foreach ($backIssues as $issue) {
			if ($issue->getCurrent()) {
				$issueOptions[] = [
					'value' => $issue->getId(),
					'label' => $issue->getIssueIdentification(),
				];
				break;
			}
		}
		foreach ($backIssues as $issue) {
			if (!$issue->getCurrent()) {
				$issueOptions[] = [
					'value' => $issue->getId(),
					'label' => $issue->getIssueIdentification(),
				];
			}
		}

		// Section options
		$sections = \Services::get('section')->getSectionList($publicationContext->getId());
		$sectionOptions = [];
		foreach ($sections as $section) {
			$sectionOptions[] = [
				'label' => $section['title'],
				'value' => (int) $section['id'],
			];
		}

		$this->addField(new FieldSelect('issueId', [
				'label' => __('issue.issue'),
				'options' => $issueOptions,
				'value' => $publication->getData('issueId') ? $publication->getData('issueId') : 0,
			]))
			->addField(new FieldSelect('sectionId', [
				'label' => __('section.section'),
				'options' => $sectionOptions,
				'value' => (int) $publication->getData('sectionId'),
			]))
			->addField(new FieldUploadImage('coverImage', [
				'label' => __('editor.article.coverImage'),
				'value' => $publicationContext->getData('coverImage'),
				'isMultilingual' => true,
				'baseUrl' => $baseUrl,
				'options' => [
					'url' => $temporaryFileApiUrl,
				],
			]))
			->addField(new FieldText('pages', [
				'label' => __('editor.issues.pages'),
				'value' => $publication->getData('pages'),
			]));
	}
}
