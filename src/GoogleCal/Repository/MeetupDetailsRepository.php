<?php

namespace GoogleCal\Repository;

use Doctrine\DBAL\Connection;
use GoogleCal\Entity\MeetupDetails;

/**
 * MeetupDetails repository
 */
class MeetupDetailsRepository
{
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function find($id)
    {
        $sql = 'SELECT * FROM meetup_details WHERE id = ?';
        $meetupDetailsData = $this->db->fetchAssoc($sql, array($id));
        $meetupDetails = $this->buildMeetupDetails($meetupDetailsData);
        return $meetupDetails;
    }

    public function findByMeetupId($meetupId)
    {
        $sql = 'SELECT * FROM meetup_details WHERE meetup_id = ?';
        $meetupDetailsData = $this->db->fetchAssoc($sql, array($meetupId));
        $meetupDetails = $this->buildMeetupDetails($meetupDetailsData);
        return $meetupDetails;
    }

    public function save(MeetupDetails $meetupDetails)
    {
        $meetupDetailsData = array(
            'meetup_id' => $meetupDetails->getMeetupId(),
            'name' => $meetupDetails->getName(),
            'thumbnail' => $meetupDetails->getThumbnail(),
            'access_token' => $meetupDetails->getAccessToken(),
            'refresh_token' => $meetupDetails->getRefreshToken()
        );

        $expires = $meetupDetails->getExpires();
        if ($expires != null) {
            $meetupDetailsData = array_merge($meetupDetailsData, array('expires' => $expires->format('Y-m-d H:i:s')));
        }

        $meetupDetailsId = $meetupDetails->getId();
        if ($meetupDetailsId) {
            $this->db->update('meetup_details', $meetupDetailsData, array('id' => $meetupDetailsId));
        } else {
            $this->db->insert('meetup_details', $meetupDetailsData);
            $id = $this->db->lastInsertId();
            $meetupDetails->setId($id);
        }

        return $meetupDetails;
    }

    private function buildMeetupDetails($meetupDetailsData)
    {
        if (!$meetupDetailsData) {
            return null;
        }

        $meetupDetails = new MeetupDetails();
        $meetupDetails->setId($meetupDetailsData['id']);
        $meetupDetails->setMeetupId($meetupDetailsData['meetup_id']);
        $meetupDetails->setName($meetupDetailsData['name']);
        $meetupDetails->setThumbnail($meetupDetailsData['thumbnail']);
        $meetupDetails->setAccessToken($meetupDetailsData['access_token']);
        $meetupDetails->setRefreshToken($meetupDetailsData['refresh_token']);
        if ($meetupDetailsData['expires'] != null) {
            $expires = new \DateTime($meetupDetailsData['expires']);
            $meetupDetails->setExpires($expires);
        }
        return $meetupDetails;
    }
}