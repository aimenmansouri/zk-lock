<?php


interface CardManagmentInterface
{
    public function addCard($cardData);
    public function getCard($cardId);
    public function deleteCard($cardId);
    public function getAllLocks();
}

class CardManagment implements CardManagmentInterface
{
    public function addCard($cardData)
    {
        echo "add card";
    }

    public function getCard($cardId)
    {
        echo "get card";
    }

    public function deleteCard($cardId)
    {
        echo "delete card";
    }

    public function getAllLocks()
    {
        echo "get all locks";
    }
}