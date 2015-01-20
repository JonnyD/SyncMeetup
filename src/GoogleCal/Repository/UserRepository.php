<?php

namespace GoogleCal\Repository;

use GoogleCal\Entity\User;

use Doctrine\DBAL\Connection;

/**
 * User repository
 */
class UserRepository
{
    private $db;
    private $meetupDetailsRepository;
    private $googleDetailsRepository;

    public function __construct(Connection $db,
                                MeetupDetailsRepository $meetupDetailsRepository,
                                GoogleDetailsRepository $googleDetailsRepository)
    {
        $this->db = $db;
        $this->meetupDetailsRepository = $meetupDetailsRepository;
        $this->googleDetailsRepository = $googleDetailsRepository;
    }

    public function find($id)
    {
        $sql = 'SELECT * FROM user WHERE id = ?';
        $userData = $this->db->fetchAssoc($sql, array($id));
        $user = $this->buildUser($userData);
        return $user;
    }

    public function findByMeetupId($meetupId)
    {
        $sql = 'SELECT user.* FROM user
                JOIN meetup_details on user.meetup_details_id = meetup_details.id
                WHERE meetup_details.meetup_id = ?';
        $userData = $this->db->fetchAssoc($sql, array($meetupId));
        $user = $this->buildUser($userData);
        return $user;
    }

    public function findByGoogleId($googleId)
    {
        $sql = 'SELECT * FROM USER
                WHERE google_details_id = ?';
        $userData = $this->db->fetchAssoc($sql, array($googleId));
        $user = $this->buildUser($userData);
        return $user;
    }

    public function save(User $user)
    {
        $userData = array(
            'id' => $user->getId()
        );

        $meetupDetails = $user->getMeetupDetails();
        if ($meetupDetails) {
            $userData = array_merge($userData, array('meetup_details_id' => $meetupDetails->getId()));
        }

        $googleDetails = $user->getGoogleDetails();
        if ($googleDetails) {
            $userData = array_merge($userData, array('google_details_id' => $googleDetails->getId()));
        }

        $userId = $user->getId();
        if ($userId) {
            $this->db->update('user', $userData, array('id' => $userId));
        } else {
            $this->db->insert('user', $userData);
            $id = $this->db->lastInsertId();
            $user->setId($id);
        }

        return $user;
    }

    private function buildUser($userData)
    {
        if (!$userData) {
            return null;
        }

        $meetupDetails = $this->meetupDetailsRepository->find($userData['meetup_details_id']);
        $googleDetails = $this->googleDetailsRepository->find($userData['google_details_id']);

        $user = new User();
        $user->setId($userData["id"]);
        $user->setMeetupDetails($meetupDetails);
        $user->setGoogleDetails($googleDetails);
        return $user;
    }
}